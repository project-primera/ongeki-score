# ProjectPrimera/ongeki-score

[![latest version](https://img.shields.io/github/v/release/projectprimera/ongeki-score?style=plastic)](https://github.com/ProjectPrimera/ongeki-score/releases/latest)
[![docker image](https://img.shields.io/badge/docker-projectprimera%2Fongeki--score-13618c?style=plastic)](https://hub.docker.com/r/projectprimera/ongeki-score)
[![docker pulls](https://img.shields.io/docker/pulls/projectprimera/ongeki-score?style=plastic)](https://hub.docker.com/r/projectprimera/ongeki-score)

![Pull requests: master](https://github.com/ProjectPrimera/ongeki-score/workflows/Pull%20requests:%20master/badge.svg?branch=develop)
![push: master](https://github.com/ProjectPrimera/ongeki-score/workflows/push:%20master/badge.svg)

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

Docker 19.03.8+

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
