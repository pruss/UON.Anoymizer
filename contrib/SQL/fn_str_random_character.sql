SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES';

DELIMITER //
DROP FUNCTION IF EXISTS str_random_character;
//

CREATE FUNCTION str_random_character(p_char VARCHAR(1))
    RETURNS VARCHAR(1)
    NO SQL
    BEGIN
    /**
    * String function. Returns random character based on a mask
    * <br>
    * %author Ronald Speelman
    * %version 1.5
    * Example usage:
    * SELECT str_random_character('d') AS digit;
    * SELECT str_random_character('C') AS UPPER;
    * See more examples and a description on www.moinne.com/blog/ronald
    *
    * %param p_pattern String: the pattern describing the random values
    *                          c returns lower-case character [a-z]
    *                          C returns upper-case character [A-Z]
    *                          A returns either upper or lower-case character [a-z A-Z]
    *                          d returns a digit [0-9]
    *                          D returns a digit without a zero [1-9]
    *                          b returns a bit [0-1]
    *                          X returns hexedecimal character [0-F]
    *                          * returns characters, decimals and special characters [a-z A-Z 0-9 !?-_@$#]
    *                          All other characters are taken literally
    * %return VARCHAR(1)
    */

    DECLARE v_result   VARCHAR(1) DEFAULT '';

        CASE p_char
            WHEN BINARY '*' THEN SET v_result := ELT(1 + FLOOR(RAND() * 69),'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
                                                                                 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                                                                                 '!','?','-','_','@','$','#',
                                                                                 0,1,2,3,4,5,6,7,8,9);
            WHEN BINARY 'A' THEN SET v_result := ELT(1 + FLOOR(RAND() * 52),'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
                                                                                 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
            WHEN BINARY 'c' THEN SET v_result := ELT(1 + FLOOR(RAND() * 26),'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
            WHEN BINARY 'C' THEN SET v_result := ELT(1 + FLOOR(RAND() * 26),'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
            WHEN BINARY 'd' THEN SET v_result := ELT(1 + FLOOR(RAND() * 10), 0,1,2,3,4,5,6,7,8,9);
            WHEN BINARY 'D' THEN SET v_result := ELT(1 + FLOOR(RAND() * 9), 1,2,3,4,5,6,7,8,9);
            WHEN BINARY 'X' THEN SET v_result := ELT(1 + FLOOR(RAND() * 16), 0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F');
            WHEN BINARY 'b' THEN SET v_result := ELT(1 + FLOOR(RAND() * 2), 0,1);
            ELSE
                SET v_result := p_char;
        END CASE;

   RETURN v_result;
END;
//
DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
