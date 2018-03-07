NAMESPACE := raymondelooff
APP_NAME := php-caching-benchmarks
VERSION := $(shell git describe --tags --always --dirty)
DOCKER_IMAGE_TAG := ${NAMESPACE}/${APP_NAME}

all: build

build:
	docker build -t ${DOCKER_IMAGE_TAG}:${VERSION} -t ${DOCKER_IMAGE_TAG}:latest .

build-no-cache:
	docker build --no-cache -t ${DOCKER_IMAGE_TAG} .

push:
	docker push ${DOCKER_IMAGE_TAG}:${VERSION}
	docker push ${DOCKER_IMAGE_TAG}:latest
