# Default Parameters
layout=local

include build.properties

all: sync

sync:
	@echo "[SVN] Synchronizing with SVN server..."
	@svn update

sync-only:
	@echo "[SVN] Synchronizing with SVN server... (omit externals)"
	@svn update --ignore-externals


deploy:
	@echo "[SSH] Deploying to server..."
	
	@svn info > web/shared/last-version.txt
	@svn status >> web/shared/last-version.txt
	@svn info | grep Revision | cut -d: -f2 > web/shared/.revision

	@if [ -n "$(deploy.$(layout).hosts)" ]; then \
		for i in $(deploy.$(layout).hosts); do \
			rsync -Cavuz --chmod=ugo=rwX -e "ssh -p$(deploy.$(layout).port)" $(deploy.$(layout).ignore) $(dir.deploy)/ "$(deploy.$(layout).user)"@$$i:"$(deploy.$(layout).root)"; \
			ssh -p$(deploy.$(layout).port) $(deploy.$(layout).user)@$$i "chmod +x $(deploy.$(layout).root)/post_deploy.sh && $(deploy.$(layout).root)/post_deploy.sh $(deploy.$(layout).root)";  \
		done; \
	else  \
		rsync -Cavuz --chmod=ugo=rwX -e "ssh -p$(deploy.$(layout).port)" $(deploy.$(layout).ignore) $(dir.deploy)/ "$(deploy.$(layout).user)"@$(deploy.$(layout).host):"$(deploy.$(layout).root)"; \
		ssh -p$(deploy.$(layout).port) $(deploy.$(layout).user)@$(deploy.$(layout).host) "chmod +x $(deploy.$(layout).root)/post_deploy.sh && $(deploy.$(layout).root)/post_deploy.sh $(deploy.$(layout).root)";  \
	fi;
	
	@rm web/shared/last-version.txt