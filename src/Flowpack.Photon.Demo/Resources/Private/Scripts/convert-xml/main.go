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
	title := doc.Find("title").Text()
	content, err := extractContent(doc)
	if err != nil {
		return nil, errors.Wrap(err, "extracting content")
	}

	data := struct {
		Type       string `yaml:"__type"`
		Title      string `yaml:"title"`
		Text       string `yaml:"text"`
		ChildNodes struct {
			Content struct {
				ChildNodes []interface{} `yaml:"__childNodes"`
			} `yaml:"content"`
		} `yaml:"__childNodes"`
	}{
		Type:  "Flowpack.Photon.Demo:Document.Book.Page",
		Title: title,
		Text:  "",
	}
	data.ChildNodes.Content.ChildNodes = content

	return data, nil
}

func extractContent(doc *goquery.Document) ([]interface{}, error) {
	var content []interface{}

	doc.Find(".body > .logo").Each(func(i int, sel *goquery.Selection) {
		imgEl := sel.Find("img")
		node := struct {
			Type  string `yaml:"__type"`
			Image string `yaml:"image"`
			Alt   string `yaml:"alt"`
			Title string `yaml:"title"`
		}{
			Type:  "Flowpack.Photon.Demo:Content.Book.Logo",
			Image: imgEl.AttrOr("src", ""),
			Alt:   imgEl.AttrOr("alt", ""),
			Title: imgEl.AttrOr("title", ""),
		}
		content = append(content, node)
	})

	doc.Find(".infos").Each(func(i int, sel *goquery.Selection) {
		vals := make(map[string]string)
		contents := sel.Contents()
		for i, n := range contents.Nodes {
			if n.Type == 3 && n.Data == "b" {
				label := strings.TrimRight(n.FirstChild.Data, ":")
				vals[label] = strings.TrimSpace(contents.Get(i + 1).Data)
			}
		}
		node := struct {
			Type       string   `yaml:"__type"`
			Published  string   `yaml:"published"`
			Categories []string `yaml:"categories"`
			Source     string   `yaml:"source"`
		}{
			Type:       "Flowpack.Photon.Demo:Content.Book.Infos",
			Published:  vals["Published"],
			Categories: trimSplit(vals["Categorie(s)"]),
			Source:     vals["Source"],
		}
		content = append(content, node)
	})

	return content, nil
}

func trimSplit(s string) []string {
	strs := strings.Split(s, ",")
	for i, s := range strs {
		strs[i] = strings.TrimSpace(s)
	}
	return strs
}

func discardErr(s string, err error) interface{} {
	return s
}
