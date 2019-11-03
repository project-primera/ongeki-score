# ProjectPrimera/ongeki-score

[![Latest version.](https://img.shields.io/github/v/release/projectprimera/ongeki-score.svg?style=plastic)](https://github.com/ProjectPrimera/ongeki-score/releases/latest)
[![Latest pre-release version.](https://img.shields.io/github/v/release/projectprimera/ongeki-score.svg?include_prereleases&style=plastic)](https://github.com/ProjectPrimera/ongeki-score/releases)
[![Docker pulls](https://img.shields.io/docker/pulls/projectprimera/ongeki-score.svg?style=plastic)](https://hub.docker.com/r/projectprimera/ongeki-score)

[![Release: Publish To Docker Hub](https://github.com/ProjectPrimera/ongeki-score/workflows/Release:%20Publish%20To%20Docker%20Hub/badge.svg)](https://github.com/ProjectPrimera/ongeki-score/actions?query=workflow%3A%22Release%3A+Publish+To+Docker+Hub%22)
[![Test: master branch](https://github.com/ProjectPrimera/ongeki-score/workflows/Test:%20master%20branch/badge.svg)](https://github.com/ProjectPrimera/ongeki-score/actions?query=workflow%3A%22Test%3A+master+branch%22)

## 概要

OngekiScoreLogはSEGAのアーケード音楽ゲーム「オンゲキ」のスコアを集計し、見やすくソートしたりできる非公式ツールです。  
他のユーザーにスコアを共有できます。  
このツールはファンメイドであり、SEGA様および関係各社には一切関係ございません。

## ビルド

```sh
docker build ./ -t projectprimera/ongeki-score
```

## 開発環境

Visual Studio Code

## 動作環境

PHP 7.3.11+  
MariaDB 10.4.8+  

## コミット時のプレフィックスルール

- feat: 機能追加
- fix: バグ修正
- docs: ドキュメントの追加修正
- style: コードスタイルの修正（たとえばシングルクォーテーションをダブルクォーテーションに変更など）
- refactor: 挙動に影響を及ぼさないコード変更
- perf: パフォーマンス向上のための変更
- test: テストケースやそれに付随するコードの追加修正
- chore: ビルドに必要なコードやライブラリ、gitの設定などの変更
- design: サイトなどに使用するリソースなど、コード以外の追加修正

参考: [angular.js/DEVELOPERS.md](https://github.com/angular/angular.js/blob/master/DEVELOPERS.md#type)

## ライセンス

GNU Affero General Public License v3.0
