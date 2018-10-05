<?php
//$config = [
//    'number' => '<option>{{text}}</option>',
//    'current' => '<option selected >{{text}}</option>',
//];
    $config = [
        'number' => '<span class="active"><a href="{{url}}">{{text}}</a></span> ',
        'current' => '<span class="active"><a href="#">{{text}}</a></span>',
        'nextActive' => '<span class="active" ><a aria-label="Next" href="{{url}}">{{text}}</a></span>',
        'nextDisabled' => '<span class="next disabled"><a aria-label="Next"><span aria-hidden="true">»</span></a></span>',
        'prevActive' => '<span class="active"><a aria-label="Previous" href="{{url}}">{{text}}</a></span>',
        'prevDisabled' => '<span class="prev disabled"><a aria-label="Previous"><span aria-hidden="true">«</span></a></span>'
    ];
?>