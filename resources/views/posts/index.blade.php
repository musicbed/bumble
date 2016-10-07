@extends('bumble::layouts.master')

@section('content')
<section class="main-area">
    @include('bumble::partials.sidenav')
    <main class="main-content">
        <div class="header">
            <div class="flex jcc aic acc">
                <h2 class="header__title">{{ $model->getPluralName() }}</h2>
                @if ($model->isSoftDeleting())
                    <a href="{{ route(config('bumble.admin_prefix') . '.' . $model->getPluralSlug() . '.trashed') }}" class="trashed-link">View Trashed</a>
                @endif
            </div>
            <a href="{{ route(config('bumble.admin_prefix') . '.' . $model->getPluralSlug() . '.create') }}" class="btn-create">Create {{{ str_singular($model->getModelName()) }}} &#8594;</a>
        </div>

        @if ($model->isSearchable())
            <div class="header">
                {!! Form::open(['method' => 'get', 'route' => [config('bumble.admin_prefix').'.'.$model->getPluralSlug().'.index']]) !!}
                {!! Form::textField('query', null, null, ['class' => 'full input ft2 tcg90 input--44 bgg05 mb2']) !!}
                {!! Form::button('Search', ['type' => 'submit', 'class' => '']) !!}
                {!! Form::close() !!}
            </div>
        @endif

        @include('bumble::partials.messages')

        @unless ($entries->isEmpty())
        <table class="table">
            <thead>
                <tr>
                    @if ($model->admin()->showIdInListing())
                        <th>{{ $model->admin()->getIdColumn() }}</th>
                    @endif
                    @foreach ($model->getFields() as $field)
                        @if ($field->showInListing())
                            @if ($field->getColumn() == 'active' || $field->getFieldType() == "BooleanField")
                                <th class="active-status">{{ strtoupper($field->getName()) }}</th>
                            @elseif ($field->getFieldType() == "BelongsToField")
                                <th>{{ $field->getRelatedTitle() }}</th>
                            @elseif ($field->getFieldType() == "HasOneField")
                                <th>{{ $field->getRelatedTitle() }}</th>
                            @else
                                <th>{{ strtoupper($field->getName()) }}</th>
                            @endif
                        @endif
                    @endforeach

                    <th class="">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                <tr>
                    @if ($model->admin()->showIdInListing())
                        <td>{{ $entry->{$model->admin()->getIdColumn()} }}</td>
                    @endif
                    @foreach ($model->getFields() as $field)
                        @unless ($field->showInListing() == false)
                            @if ($field->getFieldType() == 'TextField')
                                <td>{{ $entry->{$field->getColumn()} }}</td>
                            @elseif ($field->getColumn() == 'id')
                                <td><code>{{ $entry->{$field->getColumn()} }}</code></td>
                            @elseif ($field->getFieldType() == 'SlugField')
                                <td><code>{{ $entry->{$field->getColumn()} }}</code></td>
                            @elseif ($field->getFieldType() == 'DateTimeField')
                                <td><code>{{ $field->display($entry->{$field->getColumn()}) }}</code></td>
                            @elseif ($field->getFieldType() == 'BooleanField')
                                <td class="active-status"><i class="badge {{ active_color($entry->{$field->getColumn()}) }}"></i></td>
                            @elseif ($field->getFieldType() == 'DropdownField')
                                <td>{{ $field->getValue($entry->{$field->getColumn()}) }}</td>
                            @elseif ($field->getFieldType() == 'TextareaField')
                                @if ($field->getWidgetType() == 'bumble::fieldTypes.MarkdownField')
                                <td>
                                    <?php $output = app()->make('\League\CommonMark\CommonMarkConverter')->convertToHtml($entry->{$field->getColumn()}); ?>
                                    {{ strip_tags(str_limit($output, $limit = 40, $end = '&hellip;')) }}</td>
                                @else
                                <td>
                                    {{ str_limit($entry->{$field->getColumn()}, $limit = 40, $end = '&hellip;') }}</td>
                                @endif
                            @elseif ($field->getFieldType() == 'ImageField')
                                <td>
                                    @if ($entry->{$field->getColumn()})
                                        <img src="{{ $field->getCachedUrl($entry->{$field->getColumn()}) }}" alt="{{ $entry->{$field->getColumn()} }}" width="50" class="imax">
                                    @else
                                        &hellip;
                                    @endif
                                </td>
                            @elseif ($field->getFieldType() == 'S3FileField')
                                <td>
                                    @if ($entry->{$field->getColumn()})
                                        <img src="{{ $field->getCachedUrl($entry->{$field->getColumn()}) }}" alt="{{ $entry->{$field->getColumn()} }}" width="50">
                                        @else
                                        &hellip;
                                    @endif
                                </td>
                            @elseif ($field->getFieldType() == 'HasOneField')
                                <td>{{ $model->{$field->method()}()->getRelated()->whereId($entry->{$field->getColumn()})->pluck($field->getRelatedTitleColumn()) }}</td>
                            @elseif ($field->getFieldType() == 'BelongsToField')
                                <td>{{ $model->{$field->method()}()->getRelated()->whereId($entry->{$field->getColumn()})->pluck($field->getRelatedTitleColumn()) }}</td>
                            @else
                                <td>{{ $entry->{$field->getColumn()} }}</td>
                            @endif
                        @endunless
                    @endforeach
                    <td width="90">
                        <div class="inline-flex">
                            <a href="{{ route(config('bumble.admin_prefix').'.'.$model->getPluralSlug().'.edit', ['id' => $entry->id]) }}" class="edit-post">Edit</a>
                            {!! BumbleForm::open(['method' => 'delete', 'route' => [config('bumble.admin_prefix').'.'.$model->getPluralSlug().'.destroy', $entry->id]]) !!}
                            {!! BumbleForm::hidden('id', $entry->id) !!}
                            {!! BumbleForm::button('', ['type' => 'submit', 'class' => 'delete-post js-delete-post']) !!}
                            {!! BumbleForm::close() !!}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pv">
            {!! $entries->render() !!}
        </div>
        @else
            <div class="info-box">
                <p>You haven&rsquo;t create any {{ strtolower($model->getPluralName()) }} yet.
                    <a href="{{ route(config('bumble.admin_prefix') . '.' . $model->getPluralSlug() . '.create') }}">Create a new {{{ str_singular($model->getModelName()) }}} &#8594;</a>
                </p>
            </div>
        @endunless
    </main>
</section>
@stop
