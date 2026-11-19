<?php
namespace App\OngekiScoreLog;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * ユーザー一覧（/user）の並び順と検索語。
 * URL クエリの sort / order / q を読み取り、不正な指定は既定値（Update の降順・検索なし）に寄せる。
 * 一覧の中のリンクに付けるクエリもここで組み立てる。
 */
final class UserListCondition
{
    /** sort の値 => [user_status のカラム, 列見出しを最初に押したときの向き] */
    public const SORTS = [
        'id' => ['user_id', 'asc'],
        'name' => ['name', 'asc'],
        'trophy' => ['trophy', 'asc'],
        'lv' => ['level', 'desc'],
        'rating' => ['rating', 'desc'],
        'max' => ['rating_max', 'desc'],
        'bp' => ['battle_point', 'desc'],
        'update' => ['updated_at', 'desc'],
    ];

    public const DEFAULT_SORT = 'update';

    public const KEYWORD_MAX_LENGTH = 100;

    private string $sort;
    private string $order;
    /** URL で並び順が指定されているか（列見出しを押して並び替えた状態か） */
    private bool $sorted;
    private ?string $keyword;

    private function __construct(string $sort, string $order, bool $sorted, ?string $keyword)
    {
        $this->sort = $sort;
        $this->order = $order;
        $this->sorted = $sorted;
        $this->keyword = $keyword;
    }

    public static function fromRequest(Request $request): self
    {
        $sort = $request->query('sort');
        $sorted = is_string($sort) && isset(self::SORTS[$sort]);
        if (!$sorted) {
            // sort が無い・未知のときは order も見ない
            return new self(self::DEFAULT_SORT, self::SORTS[self::DEFAULT_SORT][1], false, self::normalizeKeyword($request->query('q')));
        }

        $order = $request->query('order');
        if ($order !== 'asc' && $order !== 'desc') {
            $order = self::SORTS[$sort][1];
        }

        return new self($sort, $order, true, self::normalizeKeyword($request->query('q')));
    }

    /**
     * 前後の空白（全角空白を含む）を除いて先頭 100 文字に切り詰める。
     * 文字列でない値（配列など）・UTF-8 として不正な値・空文字は検索なしとして扱う。
     */
    private static function normalizeKeyword($value): ?string
    {
        if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
            return null;
        }

        $keyword = mb_substr(Str::trim($value), 0, self::KEYWORD_MAX_LENGTH, 'UTF-8');

        return $keyword === '' ? null : $keyword;
    }

    public function sort(): string
    {
        return $this->sort;
    }

    public function order(): string
    {
        return $this->order;
    }

    /** 並び替えに使う user_status のカラム名 */
    public function column(): string
    {
        return self::SORTS[$this->sort][0];
    }

    /** 検索語。検索していなければ null */
    public function keyword(): ?string
    {
        return $this->keyword;
    }

    public function isSorted(): bool
    {
        return $this->sorted;
    }

    /** Rating / Max の並び替えでは、向きに関係なく現行バージョンのユーザーを先に並べる */
    public function prioritizesCurrentVersion(): bool
    {
        return $this->sort === 'rating' || $this->sort === 'max';
    }

    /**
     * ページ送りと検索フォームが引き継ぐクエリ。
     * sort / order は並び替えた状態でだけ付ける（/user や /user?page=2 からのリンクには付けない）。
     */
    public function query(): array
    {
        $query = $this->queryWithoutKeyword();
        if ($this->keyword !== null) {
            $query['q'] = $this->keyword;
        }

        return $query;
    }

    /** 検索を解除するリンクのクエリ */
    public function queryWithoutKeyword(): array
    {
        return $this->sorted ? ['sort' => $this->sort, 'order' => $this->order] : [];
    }

    /**
     * 列見出しを押したときのクエリ。
     * 現在の列なら逆向き、ほかの列ならその列の最初の向き。ページは引き継がないので 1 ページ目に戻る。
     */
    public function sortQuery(string $sort): array
    {
        if ($sort === $this->sort) {
            $order = $this->order === 'asc' ? 'desc' : 'asc';
        } else {
            $order = self::SORTS[$sort][1];
        }

        $query = ['sort' => $sort, 'order' => $order];
        if ($this->keyword !== null) {
            $query['q'] = $this->keyword;
        }

        return $query;
    }

    /** その列が現在の並び替えの列なら向き、そうでなければ null */
    public function orderOf(string $sort): ?string
    {
        return $sort === $this->sort ? $this->order : null;
    }
}
