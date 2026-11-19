<?php

namespace App;

use App\OngekiScoreLog\UserListCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class UserStatus extends Model
{
    protected $table = "user_status";
    protected $guarded = ['id'];

    function getRecentUserData($id, $exclude_private = true)
    {
        $sql = DB::table($this->table)->select('*')
            ->from($this->table)->where('user_id', $id)->orderBy('id', 'desc')->limit(1);

        $me = \Auth::user();

        // 管理者には見せる
        if ($me !== null && $me->role === 7) {
            $exclude_private = false;
        }

        # プライベートユーザを除外（自分自身以外）
        if ($exclude_private && ($me === null || $id != $me->id)) {
            $sql = $sql
                ->join('users', "$this->table.user_id", '=', 'users.id')
                ->where('users.private', 0);
        }

        return $sql->select("$this->table.*")->get();
    }

    function getRecentAllUserData($exclude_private = true)
    {
        # プライベートユーザを除外
        if ($exclude_private) {
            # SELECT * FROM user_status AS t1
            #     WHERE created_at = (
            #         SELECT MAX(created_at) FROM user_status AS t2
            #             WHERE t1.user_id = t2.user_id
            #     )
            $sql_latest_status = DB::table("$this->table as t2")
                ->select('user_id', DB::raw('MAX(created_at) as latest_created_at'))
                ->groupBy('user_id');

            $sql_all_users = DB::table("$this->table as t1")
                ->joinSub($sql_latest_status, 'latest', function ($join) {
                    $join->on('t1.user_id', '=', 'latest.user_id')
                        ->on('t1.created_at', '=', 'latest.latest_created_at');
                });

            $sql_all_users = $sql_all_users
                ->join('users', 't1.user_id', '=', 'users.id')
                ->where('users.private', 0);

            $sql = $sql_all_users->select('t1.*')->orderBy('t1.created_at', 'desc')->get();
        } else {
            $sql = DB::select('SELECT * FROM user_status AS t1 WHERE created_at = (SELECT MAX(created_at) FROM user_status AS t2 WHERE t1.user_id = t2.user_id) ORDER BY t1.created_at DESC');
        }

        $users = [];
        foreach ($sql as $key => $value) {
            if (!in_array($value->user_id, $users)) {
                $users[] = $value->user_id;
            } else {
                unset($sql[$key]);
            }
        }
        return $sql;
    }

    /**
     * 公開ユーザーの最新ステータスを、指定の並び順で 1 ページ分取得する。
     * 検索語があれば、最新ステータスの名前にそれを含むユーザーだけに絞る。
     * Rating / Max で並べるときは、現行バージョンで更新したユーザーを先に、それより前の更新のユーザーを後ろにまとめる。
     * 最新ステータスは、そのユーザーの行のうち id が最大のもの（getRecentUserData と同じ決め方）。
     * user_id のインデックスだけで最大の id が引けるので、履歴の全行は読まない。
     *
     * @param string|null $currentVersionFrom 現行バージョンの開始日時（Y-m-d H:i:s）。null なら全員を現行バージョンとして扱う
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    function paginateLatestOfPublicUsers(UserListCondition $condition, ?string $currentVersionFrom, int $perPage)
    {
        $latest = DB::table($this->table)
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id');

        $query = DB::table("$this->table as s")
            ->joinSub($latest, 'l', 'l.id', '=', 's.id')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('u.private', 0)
            ->select('s.user_id', 's.name', 's.trophy', 's.level', 's.rating', 's.rating_max', 's.battle_point', 's.updated_at');

        if ($condition->keyword() !== null) {
            // 検索語の % と _ をワイルドカードにしない。エスケープ文字を ! にしているのは、
            // バックスラッシュだと MariaDB と SQLite で書き方が分かれるため
            $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $condition->keyword()) . '%';
            $query->whereRaw("s.name LIKE ? ESCAPE '!'", [$pattern]);
        }

        if ($condition->prioritizesCurrentVersion() && $currentVersionFrom !== null) {
            // 現行バージョンで更新したユーザー（1）を、向きに関係なく先に並べる。
            // 更新日時が無い行は画面で括弧付きになるので、並びもそれより前の更新（0）の側に入れる
            $query->orderByRaw('CASE WHEN s.updated_at >= ? THEN 1 ELSE 0 END DESC', [$currentVersionFrom]);
        }
        $query->orderBy('s.' . $condition->column(), $condition->order());
        if ($condition->column() !== 'user_id') {
            // 同じ値が並んでも、ページをまたいで順序が変わらないようにする
            $query->orderBy('s.user_id', 'asc');
        }

        // ページ番号が極端に大きいと、取り出し位置（(ページ - 1) × 件数）が整数に収まらず例外になる。
        // 収まる範囲で止めて、最終ページより先を開いたときと同じ扱い（該当なし）にする
        $page = min(Paginator::resolveCurrentPage(), intdiv(PHP_INT_MAX, $perPage));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
