imageName := projectprimera/ongeki-score
tagName := local

build:
	env DOCKER_CONTENT_TRUST=1 docker build --build-arg application_version=local_build --build-arg commit_hash=4b825dc6 --no-cache -t $(imageName):$(tagName) --file Dockerfile ./
	make dockle
	make trivy

dockle:
	docker run --rm --disable-content-trust -v /var/run/docker.sock:/var/run/docker.sock goodwithtech/dockle:latest $(imageName):$(tagName) | tee .dockle-scan

trivy:
	docker run --rm --disable-content-trust -v /var/run/docker.sock:/var/run/docker.sock -v ${PWD}/.cache:/root/.cache/ aquasec/trivy image $(imageName):$(tagName) | tee .trivy-scan

local:
	make build
	docker compose up

local-remove:
	docker compose down --rmi all --volumes --remove-orphans
	php artisan config:clear
	php artisan cache:clear
	php artisan route:clear
	php artisan view:clear
