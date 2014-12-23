<!DOCTYPE html>
<html>
<head>
    <title>Sit down.</title>
    <link href="/css/seating/style.css" rel="stylesheet" />
</head>
<body>
    <div id="tables">
        <ul id="guest-list">
            @foreach ($guests as $g)
                <li data-guest="{{{ $g->id }}}">
                    <a href="#" class="remove">x</a> {{{ $g->name }}}
                </li>
            @endforeach
        </ul>
        <div id="save-wrapper">
            <a href="#" id="toggle-save">Save Seating</a>
            <div id="save-success">Good to go!<br />All saved.</div>
            <div id="save-form">
                <input type="text" id="save-title" placeholder="Title" />
                <a href="#" id="save-submit">Save</a>
            </div>
        </div>

        @foreach ($tables as $table)
            <div class="table available table-max-{{{ $table->max }}}"
                data-table="{{{ $table->id }}}"
                data-max-people="{{{ $table->max }}}"
                id="table-{{{ $table->id }}}"
                style="{{ $table->position }}">
                <h2>
                    {{{ $table->name }}}
                    (<span class="count">0</span>/<span class="total">{{{ $table->max }}}</span>)
                </h2>
                <ul></ul>
            </div>
        @endforeach

        <ul id="all-seating">
        @foreach ($all_seating as $saved_seating)
            <li>
                <a href="/sit-down/{{{ $saved_seating->id }}}">
                    {{{ $saved_seating->name }}}
                    ({{{ $saved_seating->created_at->format('m/d/Y h:iA') }}})
                </a>
            </li>
        @endforeach
        </ul>
    </div>
    <script type="text/javascript">var seating = {{ $seating_json }};</script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/ui/1.11.2/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/js/seating/main.js"></script>
</body>
</html>
