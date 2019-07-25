# Coding Style Guide

## JS

* Method and variable names must be in lowerCamelCase.
```js
var firstName = "John";
```
* Arrays that span across multiple lines can have a trailing comma to make sure that adding new rows does not change the previous row, as well.
```js
var myTab = [];

var shortTab = ['first', 'second'];

var longTab = [
    'first',
	'second',
	'...'
];

var assoTab = {
    "val1":10,
    "val2":55,
    "val3":30
};
```
* Use the else if statement to specify a new condition if the first condition is false.
```js
if (time < 10) {
    greeting = "Good morning";
} else if (time < 20) {
    greeting = "Good day";
} else {
    greeting = "Good evening";
}
```
* Put spaces around operators ( = + - * / ), and after commas.
```js
var x = y + z;
var values = [1, 2, 3]; 

var i;
for (i = 0; i < 5; i++) {
    x += i;
}
```
* Prefer 2 spaces for indentation of code blocks.
```js
function hello(world) {
  return world;
}
```
* The limit on line length must be 120 characters, 80 is better.
```js
document.getElementById("world").innerHTML =
    "Hello World.";
```
* Declarations on Top
```js
// Declare at the beginning
var firstName, lastName;

// Use later
firstName = "John";
lastName = "Doe";
```
* Declarations on Top
```js
// Declare and initiate at the beginning
var firstName = "",
    price = 0,
    myArray = [],
    myObject = {}; 
```
* Reduce Activity in Loops
```js
// bad
for (var i = 0; i < arr.length; i++) {}

//good
var i;
var l = arr.length;
for (i = 0; i < l; i++) {}
```
* Avoid Unnecessary Variables
```js
// Declare and initiate at the beginning
//bad
var fullName = firstName + " " + lastName;
document.getElementById("name").innerHTML = fullName;

//good
document.getElementById("name").innerHTML = firstName + " " + lastName 
```

**[⬆ back to top](#coding-style-guide)**

**[← back to summary](https://github.com/centreon/centreon)**
