
<form method="get" action="{{admin_url($action)}}">
  <div class="form-group">
    <label for="search">検索ワードを入力</label>
    <input required type="text" class="form-control" id="youtube_search_query" name="youtube_search_query" aria-describedby="searchHelp" placeholder="検索ワードを入力" value="{{old('youtube_search_query', $youtube_search_query ?? null)}}">
    <small id="searchHelp" class="form-text text-muted">YouTube検索を行いたいワードを入力してください。</small>
  </div>

  @if($hasKey)
  <button type="submit" class="btn btn-primary">検索</button>
  @else
  <p class="red">※YouTubeのアクセスキーが設定されていません。アクセスキーを取得し、プラグインの設定画面から、アクセスキーを保存してください。</p>
  @endif
</form>