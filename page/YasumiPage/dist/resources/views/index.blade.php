
<form method="get" action="{{admin_url($action)}}">
  <div class="form-group">
    <label for="search">祝日の検索範囲(年)を入力</label>
    <br />

    <select id="year_from" name="year_from">
      @foreach($years as $year)
      <option value="{{$year}}" {{$year == $selectYearFrom ? 'selected' : ''}}>{{$year}}</option>
      @endforeach
    </select>
    年 ～ 
    
    <select id="year_to" name="year_to">
      @foreach($years as $year)
      <option value="{{$year}}" {{$year == $selectYearTo ? 'selected' : ''}}>{{$year}}</option>
      @endforeach
    </select>

    <br />
    <small id="searchHelp" class="form-text text-muted">祝日を取得したい年の範囲を指定してください。</small>
  </div>

  @if($hasLibrary)
  <button type="submit" class="btn btn-primary">検索</button>
  @else
  <p class="red">※関連ライブラリ"azuyalabs/yasumi"がインストールされていません。以下のコマンドを実行し、関連ライブラリをインストールしてください。<br />composer require azuyalabs/yasumi</p>
  @endif
</form>