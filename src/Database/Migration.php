<?php

namespace Kurama\Database;

use Kurama\Core\Database;

/**
 * Abstract Migration Class
 */
abstract class Migration
{
    abstract public function up(Database $database): void;
    abstract public function down(Database $database): void;
}