#Database Anonymizer and Test-Data Generator

**UON.DBAnonymizer** helps you to generate

* data for testing
	* obfuscated based on existing data
	* generic from the scratch
* SQL for administrative tasks

It helps to secure personalized data like username, birthdate, email, address and more.
It can obfuscate HTML text to mask sensitive information.
It keeps required structures to enable test driven development.
It is a must when working with external developers near shore or off shore.

Configruation is easy and straight forward done with a simple structured YAML file.

**UON.DBAnonymizer** will not execute any command on your database. It just creates the required SQL to be sourced or piped into your database.

##Executing **UON.DBAnonymizer**
To execute **UON.DBAnonymizer** run from the command line

```
$./anonymize </path/to/your-config.yaml>
```

This will deliver the generated SQL-Queries to the output.
If you want to redirect the output to a file run

```
$./anonymize </path/to/your-config.yaml> > output.sql
```

To pipe the output directly into MySQL run

```
$./anonymize </path/to/your-config.yaml> | mysql -u <user> -p -D <database>
```

##Description of Configuration
To control how your data is anonymized or generated you create a YAML configuration file.
All allowed keys are in uppercase. This helps you to differentiate from your keys.

###Main Keys

| KEY | Description | Example |
| --- | ----------- | --------|
| *DIRECTORY* | directory to be used for temporary file generation | /tmp/ <br />see [DIRECTORY](#DIRECTORY)|
| *FILENAME* | filename for temporary file: will be used as first part and  random value is added  | myscript <br />see [FILENAME](#FILENAME) |
| *OPENINGS* | SQL-Files containing functions, stored procedures and scripts e.g to create tables.<br /><br />If path is relative it will try to resolve is within the package. Otherwise use absolute file path. |   contrib/SQL/fn_str_random.sql <br /> contrib/SQL/fn_str_random_character.sql <br /> contrib/SQL/fn_str_random_lipsum.sql <br />see [OPENINGS](#OPENINGS) <br />see [PROVIDED-FUNCTIONS](#PROVIDED) |
| *CLOSURE* | scripts to remove stored procedures, temporary tables | <br />see [CLOSURE](#CLOSURE) |
| *ACTIONS* | This block specifies the actions required to anonymize the date or generate it. <br > Available actions:  | <br />see [ACTIONS](#ACTIONS) |
| | *DROP* | drops the specified tables <br />see [DROP](#DROP) |
| |	*TRUNCATE* | truncates the specified tables <br />see [TRUNCATE](#TRUNCATE) |
| | *DELETE*| defines deletes queries to be executed  <br />see [DELETE](#DELETE) |
| | *INSERT*| defines insert queries to be executed  <br />see [INSERT](#INSERT) |
| | *UPDATE*| defines dedicated modification queries to be executed  <br />see [UPDATE](#UPDATE) |

### <a name="DIRECTORY"></a>DIRECTORY

### <a name="FILENAME"></a>FILENAME

### <a name="OPENINGS"></a>OPENINGS

In this section SQL user functions, procedures or SQL-scripts are defined.
This will be included into the generate SQL-output in the order of definition.

If using relative pathes it has to be relative to this packages. Otherwise use absolute pathes.

### <a name="CLOSURE"></a>CLOSURE

Similar to [OPENINGS](#OPENINGS) but this time SQL-Scripts to remove store procedure, user defined functions or temporary created tables.

Example: [closure.sql](./res/SQL/closure.sql)

### <a name="ACTIONS"></a>ACTIONS

#### <a name="DROP"></a>DROP
*Syntax*:

```
ACTIONS:
  DROP:
    TABLES:
      table_name1
      table_name2
```

*Description*:
Will generate drop queries for the provided table list

*Example* :

```
ACTIONS:
  DROP:
    TABLES:
      temp_user_copy
      temp_import_list
```

will generate:

```sql
DROP TABLE IF EXISTS temp_user_copy;
DROP TABLE IF EXISTS temp_import_list;
```


#### <a name="TRUNCATE"></a>TRUNCATE
*Syntax*:

```
ACTIONS:
  TRUNCATE:
    TABLES:
      table_name3
      table_name4
```

*Description*:
Will generate truncate queries for the provided table list

*Example* :

```
ACTIONS:
  TRUNCATE:
    TABLES:
      be_users
      fe_users
```

will generate:

```sql
TRUNCATE TABLE be_users;
TRUNCATE TABLE fe_users;
```


#### <a name="DELETE"></a>DELETE
*Syntax*:

```
ACTIONS:
  DELETE:
    QUERIES:
      your-description-as-key-to-remember1-what-is-done:
        ITEM:
        WHERE: WHERE field_name = something
        TABLES:
          table_name5
          table_name6
```

*Description*:
Will generate delete queries for the provided table list.
**WHERE** specifies the where-clause for the query. *WHERE*  **MUST** be included.
This gives more flexibility for the query. By doing this *JOIN* can be used.
**ATTENTION**: if *JOIN* is used *ITEM* **MUST** be define (see [EXAMPLE-JOIN](#EXAMPLE-JOIN))


*Example* :

```
ACTIONS:
  DELETE:
    remove-deleted-items-permanent:
      WHERE: WHERE deleted = 1
      TABLES:
        pages
        tt_content
```

will generate:

```sql
-- remove-deleted-items-permanent
DELETE FROM pages WHERE delete = 1;
DELETE FROM tt_content WHERE delete = 1;
```

<a name="EXAMPLE-JOIN"></a>
*Example with JOIN*:

```
ACTIONS:
  DELETE:
    remove-child-if-parent-page-was-removed:
      ITEMS: p1
      WHERE:p1 LEFT JOIN pages p2 ON p2.uid = p1.pid WHERE p1.pid > 0 AND p2.uid IS NULL
      TABLES:
        tt_content
        tt_news
```

will generate:

```sql
-- remove-child-if-parent-page-was-removed:
DELETE p1 FROM tt_content p1 LEFT JOIN pages p2 ON p2.uid = p1.pid WHERE p1.pid > 0 AND p2.uid IS NULL;
DELETE p1 FROM tt_news p1 LEFT JOIN pages p2 ON p2.uid = p1.pid WHERE p1.pid > 0 AND p2.uid IS NULL;
```

#### <a name="INSERT"></a>INSERT
*Syntax*:

```
ACTIONS:
  INSERT:
    TABLES:
      table_name7:
        VALUES:
          0:
            field1: value1
            field2: "value2"
            field3: f:sql-function("what-ever")
```

*Description*:
Will generate insert queries for the defined tables.
The table name has to be used as key.
Only provided fields will be inserted. String values should be quoted.
**ATTENTION**: ***f:*** indicates to use SQL build-in or stored functions!

*Example* :

```
ACTIONS:
  INSERT:
    TABLES:
      fe_users:
        VALUES:
          0:
            uid: 1
            pid: 1
            username: "tester1"
            password: f:MD5("test-1")
            email: f:str_random('c{3}c(5)[.|_]c{8}c(8)@[telekom|google|yahoo|live|mail|t-online].[com|co.uk|org|net|de]')
          1:
            uid: 2
            pid: 1
            username: "tester2"
            password: "plain-password"
            email: "paula@tester.home.de"
```

will generate:

```sql
-- INSERT
INSERT INTO fe_users(uid,pid,username,password,email) VALUES (1,1,"tester1",MD5("test1"),str_random('c{3}c(5)[.|_]c{8}c(8)@[telekom|google|yahoo|live|mail|t-online].[com|co.uk|org|net|de]');
INSERT INTO fe_users(uid,pid,username,password,email) VALUES (2,1,"tester2","plain-password","INSERT INTO fe_users(uid,pid,username,password,email) VALUES (2,1,"tester2","plain-password","paula@tester.home.de");
```

For a description of **str_-function** see [provided functions](#providedFunction).


#### <a name="UPDATE"></a>UPDATE
*Syntax*:

```
ACTIONS:
  UPDATE:
    SQL:
      table_name8:
        FIELDS:
        	field1: f:sql-function("whatever")
        	field2: "string value"
        	field3: INT value
```


*Description*:
Will generate update queries for the defined tables.
The table name has to be used as key.
Only provided fields will be updated. String values should be quoted.
**ATTENTION**: ***f:*** indicates to use SQL build-in or stored functions!



*Example* :

```
  UPDATE:
    SQL:
      tx_tcsystemstatus_items:
        FIELDS:
          title: f:str_random_lipsum(str_count(title,1),NULL, NULL, NULL)
          bodytext: f:html_lipsum(bodytext)
```

will generate:

```sql
-- UPDATE
UPDATE tx_tcsystemstatus_items SET title = str_random_lipsum(str_count(title,1),NULL, NULL, NULL),
bodytext = html_lipsum(bodytext);
```

### <a name="PROVIDED"></a>Provided Functions

This functions came with this packages.
To use these functions they must be included with the [OPENINGS](#OPENINGS)- Section of the YAML configuration file.
All functions used in the contrib/SQL section are taken from [1](#1] Ronald Speelman with some minor modifications.

| FILE | Function | Description | see |
| ---- | -------- | ------------| --- |
| contrib/SQL/fn_str_random.sql | str_random | returns a random string based on a mask| [2](#2) |
| contrib/SQL/fn_str_random_character.sql | str_random_character|returns random character based on a mask| [2](#2) |
| contrib/SQL/fn_str_random_lipsum.sql | str_random_lipsum|returns a random Lorum Ipsum string of nn words| [3](#3) |
| res/SQL/function/fn_str_count.sql | str_count | determines the number of word within a text | [str_count](#str_count) |
| res/SQL/function/fn_strip_tags.sql | str_strip_tags | removes tags from a text | [strip_tags](#strip_tags) |
| res/SQL/function/fn_html_lipsum.sql | html_lipsum | replaces text within tags by random text and keeps the structure | [html_lipsum](#html_lipsum) |

#### <a name="str_count"></a>str_count

String function. Determines the number of words within a text.

Source: [fn_str_count.sql](./res/SQL/function/fn_str_count.sql)

Examples usage:

```sql
SELECT str_count('<div class="col2"><p>This is a text</p><ul><li>item 1</li><ul></div>', 1);
SELECT str_count('The blue box is out of date', NULL);
SELECT str_count(`field_name`, 0);
```

Parameter:

|Type|Description|
|----|-----------|
|TEXT| counts the number of words of the given text|
|TINYINT| NULL will use the default <br />i.e. do not remove tags before counting <br /> 1 remove all tags before counting <br /> 0 do not removed tags |

Returns: INT

REQUIRED: [strip_tags](#strip_tags)

#### <a name="strip_tags"></a>strip_tags

String function. Removes tags from a text.

Source: [fn_strip_tags.sql](./res/SQL/function/fn_strip_tags.sql)

Examples usage:

```sql
SELECT strip_tags('<div class="col2"><p>This is a text</p><ul><li>item 1</li><ul></div>');
```

Parameter:

|Type|Description|
|----|-----------|
|TEXT| removes the tags from the given text|

Returns: TEXT

REQUIRED: no further user defined function

#### <a name="html_lipsum"></a>html_lipsum

String function. Replaces text within tags by random text and keeps the structure

Source: [fn_html_lipsum.sql](./res/SQL/function/fn_html_lipsum.sql)

Examples usage:

```sql
SELECT html_ipsum('<div class="col2"><p>This is a text</p><ul><li>item 1</li><ul></div>');
SELECT html_ipsum(`field_name`);
```

Parameter:

|Type|Description|
|----|-----------|
|TEXT| the text to be parsed and replaced|

Returns: TEXT

REQUIRED: [str_count](#str_count), [str_random_lipsum](#3)


### <a name="EXAMPLES"></a>Examples

To test this examples you should use an empty database.
In this example the database is called test and a user called ptester has granted access to it.

#### <a name="HIDE"></a>Hide Personal User Data

Before:

Table users

|uid|username|password|firstname|lastname|email|phone|
|---|--------|--------|---------|--------|-----|-----|
|1|pruss|password|Peter|Russ|prs&#64;4many.net|+49 151 123456789|
|2|paula|secrets|Paula|Meier|paula#64;home.net| |

see [table.users](./res/examples/table.users.sql)

Receipe:

```
DIRECTORY: /tmp
FILENAME: anonymize-
OPENINGS:
  contrib/SQL/fn_str_random.sql
  contrib/SQL/fn_str_random_character.sql
  contrib/SQL/fn_str_random_lipsum.sql
  res/examples/table.users.sql
CLOSURES:
  res/examples/closure.sql
ACTIONS:
  UPDATE:
    SQL:
      users:
        username: f:str_random('Cc{5}c(6)')
        passowrd: f:str_random('cc{5}c(8)')
        firstname: f:str_random('Cc{3}c(4)')
        lastname: f:str_random('Cc{5}c(7)')
        email: f:str_random('c{3}c(5)[.|_]c{8}c(8)@[telekom|google|yahoo|live|mail|t-online].[com|co.uk|org|net|de]')
        phone: f:CASE WHEN phone = '' THEN '' ELSE str_random('"+"d{1}d(3) d{7}d(3) d{8}') END
```

Run:

```
```





### <a name="PHP"></a>Used PHP-Libraries

#### <a name="SPYC"></a>Spyc
**Spyc** is a YAML loader/dumper written in pure PHP. Given a YAML document, Spyc will return an array that
you can use however you see fit. Given an array, Spyc will return a string which contains a YAML document
built from your data.
To [readmore](#4)...


### <a name="LINKS"></a>Links
1. <a name="1"> http://moinne.com/blog/ronald/tag/functions
1. <a name="2"> http://moinne.com/blog/ronald/mysql/howto-generate-meaningful-test-data-using-a-mysql-function
1. <a name="3"> http://moinne.com/blog/ronald/mysql/mysql-lorum-ipsum-generator
1. <a name="4"> https://github.com/mustangostang/spyc/


