@foreach($items as $item)
<div style="margin-bottom:3em; max-width:1000px;">
<h4 style="font-weight:bold;">{{array_get($item, 'snippet.title')}}</h4>
<img src="{{array_get($item, 'snippet.thumbnails.medium.url')}}" style="width:{{array_get($item, 'snippet.thumbnails.medium.width')}}}}; display:block;" />
<p>{{get_omitted_string(array_get($item, 'snippet.description'), 100)}}</p>
<p>再生数：{{array_get($item, 'statistics.viewCount')}}</p>
<p>評価+数：{{array_get($item, 'statistics.likeCount')}}</p>
<p>評価-数：{{array_get($item, 'statistics.dislikeCount')}}</p>
<p>投稿日時：{{\Carbon\Carbon::parse(array_get($item, 'snippet.publishedAt'))->format('Y/m/d H:i:s')}}</p>

<div style="margin-top:1em">
<form method="post" action="{{admin_url($item_action)}}">
<a href="https://www.youtube.com/watch?v={{array_get($item, 'id')}}" target="_blank" class="btn btn-primary btn-sm">
  <i class="fa fa-play" aria-hidden="true"></i>
  再生
</a>

<button type="submit" class="btn btn-success btn-sm">
  <i class="fa fa-floppy-o" aria-hidden="true"></i>
  動画ステータス保存
</button>

<input type="hidden" name="youtubeId" value="{{array_get($item, 'id')}}" />
<input type="hidden" name="thumbnail" value="{{array_get($item, 'snippet.thumbnails.medium.url')}}" />
<input type="hidden" name="description" value="{{array_get($item, 'snippet.description')}}" />
<input type="hidden" name="title" value="{{array_get($item, 'snippet.title')}}" />
<input type="hidden" name="viewCount" value="{{array_get($item, 'statistics.viewCount')}}" />
<input type="hidden" name="likeCount" value="{{array_get($item, 'statistics.likeCount')}}" />
<input type="hidden" name="dislikeCount" value="{{array_get($item, 'statistics.dislikeCount')}}" />
<input type="hidden" name="publishedAt" value="{{array_get($item, 'snippet.publishedAt')}}" />
<input type="hidden" name="url" value="https://www.youtube.com/watch?v={{array_get($item, 'id')}}" />
{{ csrf_field() }}

</form>
</div>

</div>
@endforeach
