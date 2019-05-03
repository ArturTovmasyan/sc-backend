INSERT INTO `tbl_event_definition`
(`id_space`, `title`, `show_resident_ffc`, `show_resident_ihc`, `show_resident_il`, `show_physician`,
 `show_responsible_person`, `show_additional_date`, `show_responsible_person_multi`, `show_physician_optional`,
 `show_responsible_person_optional`, `show_responsible_person_multi_optional`)
VALUES ('1', '911', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0'),
       ('1', 'Care Conference', '1', '1', '0', '0', '1', '0', '0', '0', '0', '0'),
       ('1', 'Dr. Visit', '1', '1', '0', '1', '0', '0', '0', '0', '0', '0'),
       ('1', 'Hospitalization', '1', '1', '0', '0', '0', '1', '0', '0', '0', '0'),
       ('1', 'Misc', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0'),
       ('1', 'On-Site Dr. Visit', '1', '1', '0', '1', '0', '0', '0', '0', '0', '0'),
       ('1', 'Phone Concierge', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0'),
       ('1', 'Surgery', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0');
