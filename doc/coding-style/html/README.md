# Coding Style Guide

## HTML

* All tags and attributes are lowercase.
```html
//bad
<!DOCTYPE HTML>
<div CLASS="menu">

//good
<!doctype html>
<div class="menu">
```
* Close all HTML elements.
```html
//bad
<section>
  <p>First paragraph.
  <p>Second paragraph.
</section>

//good
<section>
  <p>First paragraph.</p>
  <p>Second paragraph.</p>
</section>
```
* Close empty HTML elements.
```html
//bad
<meta charset="utf-8">
<input type="text">

//good
<meta charset="utf-8" />
<input type="text" />
```
* Always add the "alt" attribute to images.
```html
//bad
<img src="centreon.gif">

//good
<img src="centreon.gif" alt="centreon">
```
* Groups entities around equal signs
```html
//bad
<link rel = "stylesheet" href = "styles.css">

//good
<link rel="stylesheet" href="styles.css">
```
* The limit on line length must be 120 characters, 80 is better.
* Indentation
    * Do not add blank lines without a reason.
    * For readability, add blank lines to separate large or logical code blocks.
    * For readability, add 4 spaces of indentation. Do not use the tab key.
    * For readability, if the text of the block is in a straight line, the indentation is unnecessary
    * For readability, indent block elements, inline elements indentation is unnecessary
```html
//bad
<body>

    <h1>Cyprum itidem insulam procul</h1>
    <div>
    
    <h2>Paphius quin etiam</h2>
    <div>Cyprus ut nullius externi indigens adminiculi indigenis viribus a fundamento ipso carinae ad supremos
     usque carbasos aedificet onerariam navem omnibusque armamentis instructam mari committat.</div>
    </div>
   
    <p>Maximino sunt interfecti. pari sorte etiam procurator monetae extinctus est</p>
    <p>Sericum enim et Asbolium supra dictos, quoniam cum hortaretur passim 
    nominare, quos vellent, adiecta religione firmarat, nullum igni vel 
    ferro se puniri iussurum, plumbi validis ictibus interemit. </p>

</body>

//good
<body>
    <h1>Cyprum itidem insulam procul</h1>
    <div>
        <h2>Paphius quin etiam</h2>
        <div>
        Cyprus ut nullius externi indigens adminiculi indigenis viribus
        a fundamento ipso carinae ad supremos usque carbasos aedificet onerariam 
        navem omnibusque armamentis instructam mari committat.
        </div>
    </div>
    <p>Maximino sunt interfecti. pari sorte etiam procurator monetae extinctus est</p>
    <p>
    Sericum enim et Asbolium supra dictos, quoniam cum hortaretur passim 
    nominare, quos vellent, adiecta religione firmarat, nullum igni vel 
    ferro se puniri iussurum, plumbi validis ictibus interemit. 
    </p>
</body>
```
* Do not use '```&nbsp;```' and ```<br /> ```.
* Do not use old HTML tags (```<color>```, ```<front>```, ...), make a css.

**[⬆ back to top](#coding-style-guide)**

**[← back to summary](https://github.com/centreon/centreon)**