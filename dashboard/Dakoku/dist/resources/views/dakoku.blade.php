

<div class="text-center">
    <h4>{{ array_get($params, 'status_text') }}</h4>
    <p>現在日時：{{ \Carbon\Carbon::now()->format('Y/m/d H:i') }}</p>
    @foreach(array_get($params, 'buttons', []) as $button)
    <form method="post" action="{{$action}}" class="d-inline" pjax-container>
    <input type="hidden" name="action" value="{{array_get($button, 'action_name')}}" />
    <input type="submit" class="btn btn-primary btn-lg" value="{{array_get($button, 'button_text')}}" />
    
    {{ csrf_field() }}
    </form>
    @endforeach
</div>