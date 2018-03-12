#!/bin/bash

for x in src/*
do
	SHA1=`splitsh-lite --prefix=$x --quiet`
	git push "git@github.com:Flowpack/$(basename $x).git" "$SHA1":refs/heads/master
done
