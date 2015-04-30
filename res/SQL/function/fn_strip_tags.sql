SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES';

DELIMITER //
DROP FUNCTION IF EXISTS strip_tags;
//

CREATE FUNCTION strip_tags($str text) RETURNS text
  NO SQL
BEGIN
	/**
	* String function. Removes tags from a text
	* <br>
	* @author Peter Russ <peter.russ@uon.li>
	* @version 1.0
	* Example usage:
	*	select strip_tags('<div class="col2"><p>This is a text</p><ul><li>item 1</li><ul></div>');
	*
	* @param TEXT $text contains the text to be striped
	*
	* @return TEXT
	*/

    DECLARE $start, $end INT DEFAULT 1;
    LOOP
        SET $start = LOCATE("<", $str, $start);
        IF (!$start) THEN RETURN $str; END IF;
        SET $end = LOCATE(">", $str, $start);
        IF (!$end) THEN SET $end = $start; END IF;
        SET $str = INSERT($str, $start, $end - $start + 1, "");
    END LOOP;
END;
//
DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
