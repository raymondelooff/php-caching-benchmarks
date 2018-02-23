NAMESPACE := raymondelooff
APP_NAME := php-caching-benchmarks
VERSION := $(shell git describe --tags --always --dirty)
DOCKER_IMAGE_TAG := ${NAMESPACE}/${APP_NAME}:${VERSION}

all: build

build:
	docker build -t ${DOCKER_IMAGE_TAG} .

build-no-cache:
	docker build --no-cache -t ${DOCKER_IMAGE_TAG} .

push:
	docker push ${DOCKER_IMAGE_TAG}
