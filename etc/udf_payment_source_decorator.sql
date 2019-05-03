DROP FUNCTION IF EXISTS `udf_PaymentSourceDecorator`;
DELIMITER //
CREATE FUNCTION `udf_PaymentSourceDecorator`(input VARCHAR(4000)) RETURNS JSON
  DETERMINISTIC
BEGIN
  DECLARE payment_source JSON DEFAULT '[]';
  DECLARE payment_source_id INT;
  DECLARE payment_source_title VARCHAR(50);
  DECLARE payment_source_amount DOUBLE;
  DECLARE i INT DEFAULT 0;

  WHILE i < JSON_LENGTH(input) DO
  SELECT JSON_EXTRACT(input, CONCAT('$[', i, '].id')) INTO payment_source_id;
  SELECT JSON_EXTRACT(input, CONCAT('$[', i, '].amount')) INTO payment_source_amount;
  SELECT `tbl_payment_source`.`title` INTO payment_source_title
  FROM `tbl_payment_source`
  WHERE `tbl_payment_source`.`id` = payment_source_id;

  IF payment_source_title IS NOT NULL THEN
    SELECT JSON_ARRAY_APPEND(payment_source, '$',
                             JSON_OBJECT(payment_source_title, payment_source_amount)) INTO payment_source;
  END IF;

  SELECT i + 1 INTO i;
  END WHILE;

  RETURN payment_source;
END //
DELIMITER ;
