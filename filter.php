<?php

$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_ENCODED);

print_r($_GET);
