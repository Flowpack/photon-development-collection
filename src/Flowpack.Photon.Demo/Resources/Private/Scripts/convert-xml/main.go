package main

import (
	"fmt"
	"os"
	"strings"

	"github.com/PuerkitoBio/goquery"
	"github.com/pkg/errors"
	"gopkg.in/yaml.v2"
)

func main() {
	if len(os.Args) != 2 {
		fmt.Println(os.Args)
		fmt.Fprintln(os.Stderr, "expected exactly one argument: [filename]")
		os.Exit(1)
	}

	filename := os.Args[1]
	err := convertFile(filename)
	if err != nil {
		fmt.Fprintf(os.Stderr, "error converting %s: %v\n", filename, err)
		os.Exit(1)
	}
}

func convertFile(filename string) error {
	f, err := os.Open(filename)
	if err != nil {
		return errors.Wrap(err, "could not open file")
	}
	defer f.Close()

	doc, err := goquery.NewDocumentFromReader(f)
	if err != nil {
		return errors.Wrap(err, "could not create query document")
	}

	var data interface{}
	if doc.Find(".chapter").Length() > 0 {
		data, err = extractChapter(doc)
		if err != nil {
			return errors.Wrap(err, "could not extract chapter")
		}
	} else {
		data, err = extractPage(doc)
		if err != nil {
			return errors.Wrap(err, "could not extract generic page")
		}
	}

	enc := yaml.NewEncoder(os.Stdout)
	err = enc.Encode(data)
	if err != nil {
		return errors.Wrap(err, "could not marshal YAML")
	}

	return nil
}

func extractChapter(doc *goquery.Document) (interface{}, error) {
	var (
		title   string
		chapter string
	)
	if doc.Find(".chapterHeader").Length() > 0 {
		elChapterHeader := doc.Find(".chapterHeader")
		chapter = strings.TrimSpace(elChapterHeader.Text())
		elHeader := elChapterHeader.Parent()
		elChapterHeader.Remove()
		title = strings.TrimSpace(elHeader.Text())

	} else {
		title = doc.Find("title").Text()
	}

	text, err := doc.Find(".text").Html()
	if err != nil {
		return nil, errors.Wrap(err, "could not get HTML for text")
	}
	text = strings.TrimSpace(text)

	data := struct {
		Type    string `yaml:"__type"`
		Chapter string `yaml:"chapter"`
		Title   string `yaml:"title"`
		Text    string `yaml:"text"`
	}{
		Type:    "Flowpack.Photon.Demo:Document.Book.Chapter",
		Chapter: chapter,
		Title:   title,
		Text:    text,
	}

	return data, nil
}

func extractPage(doc *goquery.Document) (interface{}, error) {
	var (
		title string
	)

	title = doc.Find("title").Text()

	data := struct {
		Type  string `yaml:"__type"`
		Title string `yaml:"title"`
		Text  string `yaml:"text"`
	}{
		Type:  "Flowpack.Photon.Demo:Document.Book.Page",
		Title: title,
		Text:  "",
	}

	return data, nil
}
