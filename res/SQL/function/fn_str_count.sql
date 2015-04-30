SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES';

DELIMITER //
DROP FUNCTION IF EXISTS str_count;
//

CREATE FUNCTION str_count($text TEXT, $strip TINYINT(1)) RETURNS INT
  NO SQL
BEGIN
	/**
	* String function. Determines the number of word within a text.
	*
	* @author Peter Russ <peter.russ@uon.li>
	* @version 1.0
	* Example usage:
	*	select str_count('<div class="col2"><p>This is a text</p><ul><li>item 1</li><ul></div>', 1);
	*	select str_count('The blue box is out of date', NULL);
	*	select str_count(`field_name`, 0);
	*
	* Requires: strip_tags
	*
	* @param TEXT $text contains the text the words should be counted
	* @param TINYINT $strip <optional> if set to 1 tags will be striped before counting Default: 0 i.e. no stripping
	*
	* @return INT
	*/

     DECLARE $flag TINYINT DEFAULT 0;
     SET $flag = COALESCE($strip , $flag);

     IF $flag = 1 THEN
        SET $text = strip_tags($text);
     END IF;

     IF $text = '' THEN
        RETURN 0;
     ELSE
		SET $text = REPLACE($text, '  ', ' ');
        RETURN LENGTH($text) - LENGTH(REPLACE($text, ' ', '')) + 1;
     END IF;
END;
//
DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
