alter table `#__pbbooking_cals` drop column `gcal_username`;
alter table `#__pbbooking_cals` drop column `gcal_password`;

alter table `#__pbbooking_config` add column(
    `authcode` varchar(256) default null,
    `token` text
);
