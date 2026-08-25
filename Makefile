help:				## Show help commands
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

build:				## Build containers
	docker compose up -d --build

start:				## Run containers
	docker compose up -d

down:				## Down active containers
	docker compose down

sh:				## Exec PHP container
	docker exec -it etcd-php sh

PROTOS := Proto/auth.proto Proto/version.proto Proto/kv.proto Proto/rpc.proto

protoc:				## Regenerate PHP from .proto files. Params {{ s=SERVICE NAME }} (default: all)
	docker exec -i etcd-php sh -c 'cd /var/www/src/RPC/ && protoc -I . -I /usr/include --php_out=. --grpc_out=. --plugin=protoc-gen-grpc=`which grpc_php_plugin` $(if $(s),Proto/$(s).proto,$(PROTOS))'
