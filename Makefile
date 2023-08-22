phpstan:
	vendor/bin/phpstan analyze -c phpstan.neon -l 9 src
	
psalm:
	vendor/bin/psalm -c psalm.xml --show-info=true --no-cache

phpcs:
	vendor/bin/phpcs -s --standard=phpcs_ruleset.xml src

phpmd:
	vendor/bin/phpmd src json phpmd_ruleset.xml | jq

php-cs-fixer:
	vendor/bin/php-cs-fixer fix --using-cache=no --dry-run --diff -v src

php-cs-fixer-fix:
	vendor/bin/php-cs-fixer fix src

phpcbf:
	vendor/bin/phpcbf --standard=phpcs_ruleset.xml src

analyzers:
	@reset
	@printf "\e[1;43mRunning PHPSTAN...\e[0m\n"
	@make -s phpstan
	@printf "\e[1;42mPHPSTAN completed.\e[0m\n\n"
	
	@printf "\e[1;43mRunning PSALM...\e[0m\n"
	@make -s psalm
	@printf "\e[1;42mPSALM completed.\e[0m\n\n"
	
	@printf "\e[1;43mRunning PHPCS...\e[0m\n"
	@make -s phpcs
	@printf "\e[1;42mPHPCS completed.\e[0m\n\n"
	
	@printf "\e[1;43mRunning PHPMD...\e[0m\n"
	@make -s phpmd
	@printf "\e[1;42mPHPMD completed.\e[0m\n\n"

	@printf "\e[1;43mRunning PHP-CS-FIXER...\e[0m\n"
	@make -s php-cs-fixer
	@printf "\e[1;42mPHP-CS-FIXER completed.\e[0m\n\n"
