<?php
function sanitizar($str) {
    return htmlspecialchars(stripslashes(trim($str)));
}
