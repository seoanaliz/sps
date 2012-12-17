# Default Parameters
layout=local
branch=master

include build.properties

all: sync

sync:
	@echo "[GIT] Synchronizing with GIT server..."
	@git fetch
	@git checkout $(branch)
	@git pull
	@git status

deploy:
	@echo "[SSH] Deploying to server..."
	
	@git rev-parse HEAD > web/shared/.revision

	@if [ -n "$(deploy.$(layout).hosts)" ]; then \
		for i in $(deploy.$(layout).hosts); do \
			rsync -Cavz --chmod=ugo=rwX -e "ssh -p$(deploy.$(layout).port)" $(deploy.$(layout).params) $(deploy.$(layout).ignore) $(deploy.$(layout).dir)/ "$(deploy.$(layout).user)"@$$i:"$(deploy.$(layout).root)"; \
			ssh -p$(deploy.$(layout).port) $(deploy.$(layout).user)@$$i "chmod +x $(deploy.$(layout).root)/../post_deploy.sh && $(deploy.$(layout).root)/../post_deploy.sh $(deploy.$(layout).root)";  \
		done; \
	else  \
		rsync -Cavz --chmod=ugo=rwX -e "ssh -p$(deploy.$(layout).port)" $(deploy.$(layout).params) $(deploy.$(layout).ignore) $(deploy.$(layout).dir)/ "$(deploy.$(layout).user)"@$(deploy.$(layout).host):"$(deploy.$(layout).root)"; \
		ssh -p$(deploy.$(layout).port) $(deploy.$(layout).user)@$(deploy.$(layout).host) "chmod +x $(deploy.$(layout).root)/../post_deploy.sh && $(deploy.$(layout).root)/../post_deploy.sh $(deploy.$(layout).root)";  \
	fi;