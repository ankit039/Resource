<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

include('../bootstrap.php');
Habbo::GetUserData('rank') > 4 ?: Core::Location(URL);
?>