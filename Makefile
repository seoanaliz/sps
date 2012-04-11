# Default Parameters
layout=local

include build.properties

all: sync

sync:
	@echo "[SVN] Synchronizing with SVN server..."
	@git checkout master && git pull

sync-only:
	@echo "[SVN] Synchronizing with SVN server... (omit externals)"
	@git checkout master && git pull

deploy:
	@echo "[SSH] Deploying to server..."
	
	@git rev-parse HEAD > web/shared/.revision

	@if [ -n "$(deploy.$(layout).hosts)" ]; then \
		for i in $(deploy.$(layout).hosts); do \
			rsync -Cavuz --chmod=ugo=rwX -e "ssh -p$(deploy.$(layout).port)" $(deploy.$(layout).ignore) $(deploy.$(layout).dir)/ "$(deploy.$(layout).user)"@$$i:"$(deploy.$(layout).root)"; \
			ssh -p$(deploy.$(layout).port) $(deploy.$(layout).user)@$$i "chmod +x $(deploy.$(layout).root)/../post_deploy.sh && $(deploy.$(layout).root)/../post_deploy.sh $(deploy.$(layout).root)";  \
		done; \
	else  \
		rsync -Cavuz --chmod=ugo=rwX -e "ssh -p$(deploy.$(layout).port)" $(deploy.$(layout).ignore) $(deploy.$(layout).dir)/ "$(deploy.$(layout).user)"@$(deploy.$(layout).host):"$(deploy.$(layout).root)"; \
		ssh -p$(deploy.$(layout).port) $(deploy.$(layout).user)@$(deploy.$(layout).host) "chmod +x $(deploy.$(layout).root)/../post_deploy.sh && $(deploy.$(layout).root)/../post_deploy.sh $(deploy.$(layout).root)";  \
	fi;