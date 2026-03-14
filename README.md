# php-app

Sample PHP application that connects to a MySQL database and displays a list of friends.
Used for **Docker, Podman, Kubernetes, and OpenShift** training demos.

---

## Application Pages

| Path | Description |
|---|---|
| `/` or `/index.php` | Main page — friends list + container/pod info |
| `/test.php` | Quick pod info (hostname, IP, version) — great for rolling-update demos |
| `/health.php` | Liveness probe — returns JSON `{"status":"ok"}` |
| `/health.php?db=1` | Readiness probe — also checks DB connectivity |

---

## Environment Variables

| Variable | Description | Default |
|---|---|---|
| `DBHOST` | MySQL server hostname or IP | *(required)* |
| `MYSQL_USER` | Database username | *(required)* |
| `MYSQL_PASSWORD` | Database password | *(required)* |
| `MYSQL_DATABASE` | Database name | *(required)* |
| `APP_VERSION` | Version string shown in the UI | `1.0` |
| `APP_COLOR` | Header colour (CSS value) | `#3c6eb4` |

---

## Database Setup

Use `init.sql` to create the table and seed data (handled automatically by Docker/Podman Compose):

```sql
CREATE TABLE IF NOT EXISTS MyGuests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname  VARCHAR(255) NOT NULL
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO MyGuests (firstname, lastname) VALUES
    ('Chandler', 'Bing'), ('Rachel', 'Green'), ('Monica', 'Geller'),
    ('Dr. Ross', 'Geller'), ('Joey', 'Tribbiani Jr.'), ('Phoebe', 'Buffay');
```

---

## Docker

### Build and run with Docker Compose (recommended)

```bash
docker compose up --build
```

App is available at http://localhost:8080

### Build and run manually

```bash
# Build the image
docker build -t php-app .

# Start MySQL
docker run -d --name friends-db \
  -e MYSQL_ROOT_PASSWORD=r00tpa55 \
  -e MYSQL_DATABASE=friends \
  -e MYSQL_USER=swapnil \
  -e MYSQL_PASSWORD=redhat \
  -v $(pwd)/init.sql:/docker-entrypoint-initdb.d/init.sql \
  mysql:8.0

# Start the app (linked to MySQL)
docker run -d --name friends-app -p 8080:8080 \
  --link friends-db:db \
  -e DBHOST=db \
  -e MYSQL_USER=swapnil \
  -e MYSQL_PASSWORD=redhat \
  -e MYSQL_DATABASE=friends \
  php-app
```

### Rolling-update demo (Docker)

```bash
# Build v2 with a different colour
docker build --build-arg APP_VERSION=2.0 -t php-app:v2 .

# Update the running container
docker stop friends-app && docker rm friends-app
docker run -d --name friends-app -p 8080:8080 \
  --link friends-db:db \
  -e DBHOST=db -e MYSQL_USER=swapnil \
  -e MYSQL_PASSWORD=redhat -e MYSQL_DATABASE=friends \
  -e APP_VERSION=2.0 -e APP_COLOR=#e63946 \
  php-app:v2
```

---

## Podman

### Build and run with Podman Compose

```bash
podman compose up --build
```

### Build and run with Podman Pods

Podman pods share a network namespace — use `localhost` for inter-container communication.

```bash
# Create a pod that exposes port 8080
podman pod create --name friends-pod -p 8080:8080

# Start MySQL inside the pod
podman run -d --pod friends-pod \
  --name friends-db \
  -e MYSQL_ROOT_PASSWORD=r00tpa55 \
  -e MYSQL_DATABASE=friends \
  -e MYSQL_USER=swapnil \
  -e MYSQL_PASSWORD=redhat \
  -v $(pwd)/init.sql:/docker-entrypoint-initdb.d/init.sql \
  docker.io/mysql:8.0

# Wait for MySQL to be ready, then start the app
podman run -d --pod friends-pod \
  --name friends-app \
  -e DBHOST=localhost \
  -e MYSQL_USER=swapnil \
  -e MYSQL_PASSWORD=redhat \
  -e MYSQL_DATABASE=friends \
  localhost/php-app

# Check status
podman pod ps
podman logs friends-app
```

### Build image with Podman

```bash
podman build -t php-app .
podman images
```

---

## Kubernetes

### Deploy with manifests

```bash
kubectl apply -f k8s.yaml
kubectl get pods,svc
kubectl get svc friends-app   # get EXTERNAL-IP or NodePort
```

### Quick deploy with kubectl

```bash
# Create a namespace
kubectl create namespace friends

# Deploy MySQL
kubectl create deployment mysql -n friends \
  --image=mysql:8.0 \
  --env="MYSQL_ROOT_PASSWORD=r00tpa55" \
  --env="MYSQL_DATABASE=friends" \
  --env="MYSQL_USER=swapnil" \
  --env="MYSQL_PASSWORD=redhat"

kubectl expose deployment mysql -n friends --port=3306

# Deploy the app
kubectl create deployment friends-app -n friends \
  --image=<your-registry>/php-app:latest \
  --env="DBHOST=mysql" \
  --env="MYSQL_USER=swapnil" \
  --env="MYSQL_PASSWORD=redhat" \
  --env="MYSQL_DATABASE=friends"

kubectl expose deployment friends-app -n friends --port=8080 --type=NodePort

# Rolling update demo
kubectl set image deployment/friends-app \
  friends-app=<your-registry>/php-app:v2 -n friends

kubectl rollout status deployment/friends-app -n friends
kubectl rollout history deployment/friends-app -n friends
kubectl rollout undo deployment/friends-app -n friends   # rollback
```

### Scale demo

```bash
kubectl scale deployment friends-app --replicas=3 -n friends
# Reload the browser multiple times — hostname changes with each pod
```

### Health checks (liveness & readiness probes)

```yaml
livenessProbe:
  httpGet:
    path: /health.php
    port: 8080
  initialDelaySeconds: 15
  periodSeconds: 20

readinessProbe:
  httpGet:
    path: /health.php?db=1
    port: 8080
  initialDelaySeconds: 10
  periodSeconds: 10
```

---

## OpenShift

### Deploy with Source-to-Image (S2I)

```bash
# Create a project
oc new-project friends-demo

# Deploy MySQL from the catalogue
oc new-app mysql:8.0 \
  -e MYSQL_USER=swapnil \
  -e MYSQL_PASSWORD=redhat \
  -e MYSQL_DATABASE=friends \
  -e MYSQL_ROOT_PASSWORD=r00tpa55 \
  --name=mysql

# Wait for MySQL, then seed the database
oc wait --for=condition=ready pod -l deployment=mysql --timeout=120s
oc exec -it $(oc get pod -l deployment=mysql -o name) -- \
  mysql -uswapnil -predhat friends < init.sql

# Build and deploy the app from this Git repo using S2I
oc new-app php:8.2~https://github.com/swapnil-linux/php-app.git \
  -e DBHOST=mysql \
  -e MYSQL_USER=swapnil \
  -e MYSQL_PASSWORD=redhat \
  -e MYSQL_DATABASE=friends \
  --name=friends-app

# Expose a Route
oc expose svc friends-app

# Check status
oc get pods
oc get route friends-app
```

### Rolling update with S2I

```bash
# Trigger a new build (after pushing changes to Git)
oc start-build friends-app --follow

# Or update an env var to force a new rollout
oc set env deployment/friends-app APP_VERSION=2.0 APP_COLOR=#e63946
oc rollout status deployment/friends-app
```

### Useful oc commands for demos

```bash
oc get pods -w                        # watch pods
oc describe pod <pod-name>            # events and resource usage
oc logs <pod-name>                    # container logs
oc rsh <pod-name>                     # shell into pod
oc scale deployment friends-app --replicas=3
oc rollout undo deployment/friends-app
```

---

## Project Structure

```
php-app/
├── index.php          # Main page (friends list + container info)
├── test.php           # Pod info page (hostname, IP, version)
├── health.php         # Health check endpoint (JSON)
├── friends.jpg        # Banner image
├── init.sql           # Database schema + seed data
├── Dockerfile         # Container image (PHP 8.2 + Apache, port 8080)
├── docker-compose.yml # Local dev environment (app + MySQL 8)
├── Dockerfile.old     # Legacy Alpine-based Dockerfile (reference only)
└── .s2i/bin/assemble  # Custom OpenShift S2I build hook
```
