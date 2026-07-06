main:
	php -S localhost:4000 -t public/

docs:
	@test -f phpDocumentor.phar || curl -sL -o phpDocumentor.phar https://phpdoc.org/phpDocumentor.phar
	php phpDocumentor.phar -c phpdoc.xml --force

docs-clean:
	rm -rf docs/api .phpdoc