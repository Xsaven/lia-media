<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('lia::form.error')

        <div class="input-group">

            @if ($prepend)
                <span class="input-group-addon">{!! $prepend !!}</span>
            @endif

            <input {!! $attributes !!} />

            @if ($append)
                <span class="input-group-btn">{!! $append !!}</span>
            @endif

        </div>

        @if($prev)
            <div class="img-thumbnail" style="width: 100%; text-align: center;">
                <img id="prev_{{$name}}" src="{{$old_value}}" style="max-width: {{$max_width}}" />
                <a onclick="window.clear_{{$name}}(); return false;" class="btn btn-danger btn-sm" style="position: absolute;width: 20px;height: 20px;padding: 0;right: 21px;"><i class="fa fa-trash"></i></a>
            </div>
        @endif

        @include('lia::form.help-block')

    </div>
</div>