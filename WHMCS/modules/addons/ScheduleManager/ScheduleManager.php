<?php

use WHMCS\Database\Capsule as DB;

function ScheduleManager_config()
{
    $configarray = array(
        "name" => "Schedule Manager",
        "description" => "",
        "version" => "1.0",
        "author" => "TMD",
    );
    return $configarray;
}

function ScheduleManager_activate()
{
    DB::statement("
    CREATE TABLE IF NOT EXISTS `schedule_agentsgroups` (
        `id` int(10) unsigned NOT NULL,
        `group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `parent` int(11) NOT NULL DEFAULT '0',
        `color` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '#000',
        `bgcolor` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'rgb(202 202 202)',
        `order` int(11) DEFAULT '0'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_agents_details` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` int(11) NOT NULL,
        `color` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'null',
        `bg` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'null',
        `ldap_username` text COLLATE utf8_unicode_ci,
        `ldap_phone` text COLLATE utf8_unicode_ci,
        `ldap_email` text COLLATE utf8_unicode_ci
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    DB::statement("
      
      CREATE TABLE IF NOT EXISTS `schedule_agents_groups_editor` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` int(11) NOT NULL,
        `group_id` int(11) NOT NULL,
        `permission` int(11) DEFAULT NULL COMMENT '1-manage timetable, 2-view stats of group, 3-manage shifts of main teams'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_agents_shifts` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
        `shift_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_agents_to_groups` (
        `id` int(10) unsigned NOT NULL,
        `group_id` int(11) unsigned NOT NULL,
        `agent_id` int(11) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      
      CREATE TABLE IF NOT EXISTS `schedule_calendaraccess` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
        `hash` char(32) COLLATE utf8_unicode_ci DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      
      CREATE TABLE IF NOT EXISTS `schedule_daysoff` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` int(11) NOT NULL,
        `days` int(11) NOT NULL,
        `year` int(4) NOT NULL,
        `addedby` int(11) DEFAULT NULL,
        `created` datetime NOT NULL,
        `date_expiration` date DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      
      CREATE TABLE IF NOT EXISTS `schedule_editors` (
        `id` int(10) unsigned NOT NULL,
        `editor_id` int(11) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      
      CREATE TABLE IF NOT EXISTS `schedule_eventslog` (
        `id` int(10) unsigned NOT NULL,
        `author` int(11) NOT NULL,
        `log` text COLLATE utf8_unicode_ci NOT NULL,
        `event_date` date NOT NULL,
        `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'null',
        `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'null',
        `date` datetime NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      
      CREATE TABLE IF NOT EXISTS `schedule_feedback` (
        `id` int(10) unsigned NOT NULL,
        `author_id` int(10) unsigned NOT NULL DEFAULT '0',
        `feedback` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
        `date` datetime DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_shifts` (
        `id` int(10) unsigned NOT NULL,
        `group_id` int(11) unsigned NOT NULL,
        `from` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
        `to` varchar(64) COLLATE utf8_unicode_ci NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_shifts_hidden` (
        `id` int(10) unsigned NOT NULL,
        `shift_id` int(11) unsigned NOT NULL,
        `refdate` date NOT NULL,
        `hide` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_slackusers` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` int(11) NOT NULL,
        `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `slackid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `realname` text COLLATE utf8_unicode_ci,
        `realnamenormalized` text COLLATE utf8_unicode_ci
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_templates` (
        `id` int(10) unsigned NOT NULL,
        `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `group_id` int(11) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_timetable` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` int(11) NOT NULL,
        `group_id` int(11) unsigned NOT NULL,
        `shift_id` int(10) unsigned NOT NULL,
        `day` date NOT NULL,
        `draft` tinyint(4) NOT NULL,
        `author` int(11) NOT NULL DEFAULT '0',
        `order` int(11) DEFAULT '0'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_timetable_deldrafts` (
        `id` int(10) unsigned NOT NULL,
        `entry_id` int(11) NOT NULL,
        `author` int(11) NOT NULL DEFAULT '0'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("CREATE TABLE IF NOT EXISTS `schedule_tplshifts` (
        `id` int(10) unsigned NOT NULL,
        `tpl_id` int(11) NOT NULL,
        `day` char(1) COLLATE utf8_unicode_ci NOT NULL,
        `agent_id` int(11) NOT NULL,
        `shift_id` int(11) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_vacations` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
        `group_id` int(11) NOT NULL,
        `day` date NOT NULL,
        `draft` tinyint(3) unsigned NOT NULL,
        `author` int(11) NOT NULL DEFAULT '0'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      CREATE TABLE IF NOT EXISTS `schedule_vacations_request` (
        `id` int(10) unsigned NOT NULL,
        `agent_id` int(11) NOT NULL,
        `request_type` int(11) NOT NULL DEFAULT '1' COMMENT '1 for vacation request, 2 for shift change request',
        `date_start` date NOT NULL,
        `date_end` date NOT NULL,
        `date_submit` datetime NOT NULL,
        `desc` text COLLATE utf8_unicode_ci,
        `approve_date` datetime DEFAULT NULL,
        `approve_status` char(1) COLLATE utf8_unicode_ci NOT NULL,
        `approve_admin_id` int(11) DEFAULT '0',
        `approve_response` text COLLATE utf8_unicode_ci,
        `cancelled` int(11) DEFAULT '0',
        `excluded_days` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Comma separated dates identifying weekend'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    DB::statement("
      ALTER TABLE `schedule_agentsgroups`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_agents_details`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_agents_groups_editor`
        ADD PRIMARY KEY (`id`),
        ADD KEY `group_agent` (`agent_id`,`group_id`) USING BTREE;");

    DB::statement("
      ALTER TABLE `schedule_agents_shifts`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_agents_to_groups`
        ADD PRIMARY KEY (`id`),
        ADD KEY `schedule_agents_to_groups_group_id` (`group_id`);");

    DB::statement("
      ALTER TABLE `schedule_calendaraccess`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_daysoff`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_editors`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_eventslog`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_feedback`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_shifts`
        ADD PRIMARY KEY (`id`),
        ADD KEY `schedule_shifts_group_id` (`group_id`);");

    DB::statement("
      ALTER TABLE `schedule_shifts_hidden`
        ADD PRIMARY KEY (`id`) USING BTREE,
        ADD KEY `schedule_shifts_id` (`shift_id`);");

    DB::statement("
      ALTER TABLE `schedule_slackusers`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_templates`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_timetable`
        ADD PRIMARY KEY (`id`),
        ADD KEY `schedule_timetable_group_id` (`group_id`),
        ADD KEY `schedule_timetable_shift_id` (`shift_id`);");

    DB::statement("
      ALTER TABLE `schedule_timetable_deldrafts`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_tplshifts`
        ADD PRIMARY KEY (`id`);");

    DB::statement("
      ALTER TABLE `schedule_vacations`
        ADD PRIMARY KEY (`id`),
        ADD KEY `schedule_vacations_day_group_id_index` (`day`,`group_id`);");

    DB::statement("
      ALTER TABLE `schedule_vacations_request`
        ADD PRIMARY KEY (`id`),
        ADD KEY `schedule_vacations_index` (`agent_id`);");

    DB::statement("
      ALTER TABLE `schedule_agentsgroups`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_agents_details`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_agents_groups_editor`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_agents_shifts`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_agents_to_groups`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_calendaraccess`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_daysoff`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_editors`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_eventslog`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_feedback`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_shifts`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_shifts_hidden`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_slackusers`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_templates`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_timetable`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_timetable_deldrafts`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_tplshifts`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_vacations`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_vacations_request`
        MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
    DB::statement("
      ALTER TABLE `schedule_agents_to_groups`
        ADD CONSTRAINT `schedule_agents_to_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `schedule_agentsgroups` (`id`) ON DELETE CASCADE;");
    DB::statement("
      ALTER TABLE `schedule_shifts`
        ADD CONSTRAINT `schedule_shifts_group_id` FOREIGN KEY (`group_id`) REFERENCES `schedule_agentsgroups` (`id`) ON DELETE CASCADE;");
    DB::statement("
      ALTER TABLE `schedule_shifts_hidden`
        ADD CONSTRAINT `schedule_shifts_id` FOREIGN KEY (`shift_id`) REFERENCES `schedule_shifts` (`id`) ON DELETE CASCADE;");
    DB::statement("
      ALTER TABLE `schedule_timetable`
        ADD CONSTRAINT `schedule_timetable_group_id` FOREIGN KEY (`group_id`) REFERENCES `schedule_agentsgroups` (`id`) ON DELETE CASCADE,
        ADD CONSTRAINT `schedule_timetable_shift_id` FOREIGN KEY (`shift_id`) REFERENCES `schedule_shifts` (`id`) ON DELETE CASCADE;");

    return [
        'status' => 'success',
        'description' => 'The module has been successfuly activated.',
    ];
}

function ScheduleManager_output($vars)
{
    header('Location: ../schedule');

    echo '<p>Redirecting...</p>';
}
