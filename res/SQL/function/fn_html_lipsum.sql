SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES';

DELIMITER $$
DROP FUNCTION IF EXISTS html_lipsum;
$$

CREATE FUNCTION html_lipsum($text TEXT) RETURNS TEXT
	NO SQL
BEGIN
	/**
	* String function. Replaces text within tags by random text and keeps the structure
	*
	* @author Peter Russ <peter.russ@uon.li>
	* @version 1.0
	* Example usage:
	*	select html_ipsum('<div class="col2"><p>This is a text</p><ul><li>item 1</li><ul></div>');
	*	select html_ipsum(`field_name`);
	*
	* Requires: str_count, str_random_lipsum to be loaded
	*
	* @param TEXT $text contains the text to be parsed and replaced
	*
	* @return TEXT
	*/

	DECLARE $start, $end, $next, $pos, $len INT DEFAULT 1;
	DECLARE $new TEXT DEFAULT '';
	DECLARE $tag TEXT DEFAULT '';
	DECLARE $part, $split TEXT DEFAULT '';

	DECLARE CONTINUE HANDLER FOR SQLSTATE '22001' BEGIN RETURN '<span class="error">CONTENT TO LONG</span>'; END;

	IF ($text IS NULL ) THEN RETURN ''; END IF ;
	IF (LENGTH($text) > 65500 ) THEN RETURN '<span class="error">CONTENT TO LONG</span>'; END IF ;

	SET $text = REPLACE($text, '\b', '');
	SET $text = REPLACE($text, '\r', '');
	SET $text = REPLACE($text, '\t', '');
	SET $text = REPLACE($text, '\v', '');
	SET $text = REPLACE($text, '\f', '');

    LOOP
		SET $start = LOCATE('<', $text);
		IF (!$start) THEN
			IF ($text <> '') THEN
				SET $new = CONCAT($new, str_random_lipsum(str_count($text, 0), NULL, NULL, NULL));
			END IF;
			RETURN $new;
		END IF;
		SET $end = LOCATE('>', $text, $start);
		IF (!$end) THEN
			IF ($text <> '') THEN
				SET $new = CONCAT($new, str_random_lipsum(str_count($text, 0), NULL, NULL, NULL));
			END IF;
			RETURN $new;
		END IF;
		IF ($start != $end) THEN
			SET $tag = SUBSTRING($text, $start, $end - $start + 1);
			SET $text = INSERT($text, $start, $end - $start + 1, ' ');
			IF ($start > 1) THEN
				SET $part = SUBSTRING($text, 1, $start-1);
				IF (SUBSTRING($part,1,1) = '>') THEN
					SET $part = SUBSTRING($part,2);
				END IF;

				SET $text = INSERT($text, 1, $start, '');
				SET $pos = LOCATE('\n', $part);
				IF (!$pos) THEN
					SET $len = str_count($part,0);
					IF ($len > 0) THEN
						SET $new = CONCAT($new,str_random_lipsum($len, NULL, NULL, NULL));
					END IF ;
				ELSE
					splitLf: LOOP
						SET $split = SUBSTRING($part, 1, $pos - 1);
						SET $part = INSERT($part, 1, $pos, '');
						SET $len = LENGTH($split);
						IF ($len > 1) THEN
							SET $len = str_count($split,1);
							IF ($len > 0) THEN
								SET $new = CONCAT($new, SUBSTRING(str_random_lipsum($len, NULL, NULL, NULL), 1, $len) , '\n');
							ELSE
								SET $new = CONCAT($new, '\n');
							END IF ;
						ELSE
							SET $new = CONCAT($new, '\n');
						END IF;
						SET $pos = LOCATE('\n', $part);
						IF (!$pos) THEN
							LEAVE splitLf;
						END IF;
					END LOOP splitLf;
				END IF;
			END IF;
			SET $new = CONCAT($new, $tag);
		END IF;

    END LOOP;
END;
$$
DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
