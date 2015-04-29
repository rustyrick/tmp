#
# Encoding: Unicode (UTF-8)
#


DROP TABLE IF EXISTS `#__pbbooking_block_days`;
DROP TABLE IF EXISTS `#__pbbooking_cals`;
DROP TABLE IF EXISTS `#__pbbooking_config`;
DROP TABLE IF EXISTS `#__pbbooking_customfields`;
DROP TABLE IF EXISTS `#__pbbooking_customfields_data`;
DROP TABLE IF EXISTS `#__pbbooking_events`;
DROP TABLE IF EXISTS `#__pbbooking_treatments`;
DROP TABLE IF EXISTS `#__pbbooking_logs`;
DROP TABLE IF EXISTS `#__pbbooking_surveys`;
DROP TABLE IF EXISTS `#__pbbooking_sync`;
DROP TABLE IF EXISTS `#__pbbooking_lang_override`;


CREATE TABLE if not exists `#__pbbooking_cals` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `hours` text,
  `email` varchar(128) DEFAULT NULL,
  `enable_google_cal` tinyint(1) DEFAULT 0,
  `gcal_username` VARCHAR(256) DEFAULT NULL,
  `gcal_password` VARCHAR(256) DEFAULT NULL,
  `gcal_id` VARCHAR(256) DEFAULT NULL,
  `color` VARCHAR(16) DEFAULT '#339933',
  `languages` text default null,
  `ordering` int(11) default 0,
  `groupbookings` tinyint(1) default 0,
  `groupclass_max` int(11) default null,
  `calendar_schedule` text default null,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE if not exists `#__pbbooking_block_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `block_start_date` datetime,
  `block_end_date` datetime,
  `block_note` varchar(255) DEFAULT NULL,
  `calendars` varchar(255) DEFAULT NULL,
  `r_int` int(11) DEFAULT '0',
  `r_freq` varchar(128) DEFAULT NULL,
  `r_end` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;




CREATE TABLE if not exists `#__pbbooking_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email_body` text NOT NULL,
  `trading_hours` text,
  `email_subject` varchar(255) NOT NULL,
  `show_link` int(1) DEFAULT NULL,
  `time_groupings` text,
  `time_increment` int(11) DEFAULT '30',
  `show_prices` tinyint(1) DEFAULT '1',
  `bcc_admin` tinyint(1) DEFAULT '1',
  `validation` varchar(128) DEFAULT 'client',
  `calendar_start_day` tinyint(1) NOT NULL DEFAULT '0',
  `show_busy_frontend` tinyint(1) NOT NULL DEFAULT '0',
  `enable_logging` tinyint(1) NOT NULL DEFAULT '0',
  `auto_validated_appt_body` text,
  `auto_validated_appt_email_subject` text,
  `manage_fields` text,
  `enable_shifts` tinyint(1) DEFAULT '1',
  `currency_symbol_before` tinyint(1) DEFAULT '1',
  `admin_validation_pending_email_subject` varchar(256) DEFAULT NULL,
  `admin_validation_pending_email_body` text,
  `admin_validation_confirmed_email_subject` varchar(256) DEFAULT NULL,
  `admin_validation_confirmed_email_body` text,
  `paypal_currency` varchar(10) DEFAULT 'AUD',
  `paypal_test` tinyint(1) DEFAULT '0',
  `admin_paypal_confirm` text,
  `admin_paypal_confirm_subject` varchar(256) DEFAULT NULL,
  `client_paypal_confirm_subject` varchar(256) DEFAULT NULL,
  `client_paypal_confirm` text,
  `notification_email` varchar(256) DEFAULT NULL,
  `multi_page_checkout` tinyint(1) DEFAULT '0',
  `enable_cron` tinyint(1) DEFAULT '0',
  `enable_reminders` tinyint(1) DEFAULT '0',
  `reminder_settings` text,
  `reminder_email_body` text,
  `reminder_email_subject` varchar(256) DEFAULT NULL,
  `single_page_block_days_master_trading_hours` tinyint(1) DEFAULT '0',
  `enable_testimonials` tinyint(1) DEFAULT '0',
  `testimonial_days_after` int(11) DEFAULT '0',
  `testimonial_email_subject` varchar(256) DEFAULT NULL,
  `testimonial_email_body` text,
  `testimonial_questions` text,
  `disable_announcements` tinyint(1) DEFAULT '0',
  `self_service_change_notice` int(11) DEFAULT '48',
  `enable_selfservice` tinyint(1) DEFAULT '0',
  `enable_google_cal` tinyint(1) DEFAULT '0',
  `display_past_appointments` tinyint(1) DEFAULT '0',
  `prevent_bookings_within` int(11) DEFAULT '60',
  `disable_pending_bookings` tinyint(1) DEFAULT '0',
  `show_busy_front_end` tinyint(1) DEFAULT '0',
  `select_calendar_individual` tinyint(1) DEFAULT '0',
  `enable_multilanguage` tinyint(1) DEFAULT '0',
  `multilangmessages` text,
  `calendar_color` varchar(11) DEFAULT '#5F0044',
  `allow_booking_max_days_in_advance` int(11) DEFAULT '0',
  `color` varchar(11) DEFAULT '#5F0044',
  `user_offset` tinyint(1) DEFAULT '0',
  `booking_details_template` text,
  `enable_firephp` tinyint(1) DEFAULT '0',
  `admin_pending_cancel_subject` varchar(512) DEFAULT NULL,
  `admin_pending_cancel_body` text,
  `enable_recaptcha` tinyint(1) DEFAULT '0',
  `sync_future_events` tinyint(1) DEFAULT '0',
  `sync_google_events_to_pbbooking` tinyint(1) DEFAULT '0',
  `paypal_api_username` varchar(256) DEFAULT NULL,
  `paypal_api_password` varchar(256) DEFAULT NULL,
  `paypal_api_signature` varchar(512) DEFAULT NULL,
  `google_cal_sync_secret` varchar(80) DEFAULT NULL,
  `google_max_results` int(11) default 25,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;


CREATE TABLE if not exists `#__pbbooking_customfields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fieldname` varchar(80) DEFAULT NULL,
  `fieldtype` varchar(80) DEFAULT NULL,
  `varname` varchar(80) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `is_email` tinyint(1) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT NULL,
  `is_first_name` tinyint(1) DEFAULT '0',
  `is_last_name` tinyint(1) DEFAULT '0',
  `values` varchar(255) DEFAULT NULL,
  `ordering` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;


CREATE TABLE if not exists `#__pbbooking_customfields_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customfield_id` int(11) DEFAULT NULL,
  `pending_id` int(11) DEFAULT NULL,
  `data` varchar(256) DEFAULT NULL,
  `is_email` tinyint(1) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;


CREATE TABLE if not exists `#__pbbooking_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cal_id` int(11) NOT NULL,
  `summary` text NOT NULL,
  `dtend` datetime DEFAULT NULL,
  `dtstart` datetime DEFAULT NULL,
  `description` text NOT NULL,
  `uid` varchar(80) DEFAULT NULL,
  `service_id` int(11) DEFAULT '0',
  `r_int` int(11) DEFAULT NULL,
  `r_freq` VARCHAR(255) DEFAULT NULL,
  `r_end` datetime DEFAULT NULL,
  `customfields_data` text,
  `email` varchar(128) DEFAULT NULL,
  `deposit_paid` tinyint(1) DEFAULT '0',
  `amount_paid` decimal(10,2) DEFAULT '0.00',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `testimonial_request_sent` tinyint(1) DEFAULT 0,
  `gcal_id` varchar(256) DEFAULT NULL,
  `user_offset` int(11) DEFAULT 0,
  `verified` tinyint(1) DEFAULT 0,
  `validation_token` varchar(256) default null,
  `parent` int(11) DEFAULT 0,
  `remote_ip` varchar(45) default null,
  `date_created` datetime default null,
  `externalevent` tinyint(1) default 0,
  `deleted` tinyint(1) default 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;


CREATE TABLE if not exists `#__pbbooking_treatments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(19,4) NOT NULL DEFAULT '0.00',
  `calendar` varchar(128) DEFAULT '0',
  `require_payment` tinyint(1) DEFAULT 0,
  `ordering` int(11) default 0,
  `is_variable` tinyint(1) DEFAULT 0,
  `min_duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE if not exists `#__pbbooking_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime DEFAULT NULL,
  `component` varchar(128) DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE if not exists `#__pbbooking_surveys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT '0',
  `date_submitted` datetime DEFAULT NULL,
  `submission_ip` varchar(128) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT '0',
  `content` text,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

CREATE TABLE if not exists `#__pbbooking_sync` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_added` datetime DEFAULT NULL,
  `action` varchar(10) DEFAULT NULL,
  `data` text,
  `status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `#__pbbooking_lang_override` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT '0',
  `messagename` varchar(128) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `langtag` varchar(10) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;