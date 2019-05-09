#!/usr/bin/env bash

BASE_DIR=`dirname $0`

pushd $BASE_DIR/convert-xml
go build
popd

mkdir -p $BASE_DIR/../Fixtures/book $BASE_DIR/../Content/book
unzip -q -o $BASE_DIR/../Fixtures/Lewis-Carroll_Alices-Adventures-in-Wonderland.epub -d $BASE_DIR/../Fixtures/book

for f in $BASE_DIR/../Fixtures/book/OPS/*.xml; do
    $BASE_DIR/convert-xml/convert-xml $f > $BASE_DIR/../Content/book/$(basename "$f" .xml).yaml
done
