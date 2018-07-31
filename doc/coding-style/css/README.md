# Coding Style Guide
## CSS
### OOCSS

We encourage you to make use of [OOCSS](http://oocss.org/) for these reasons:

* It helps create clear, strict relationships between CSS and HTML
* It helps us create reusable, composable components
* It allows for less nesting and lower specificity
* It helps in building scalable stylesheets

```html
<div class="centreon centreon-block">
    <h1 class="centreon-title">Caesaribus velut parens gentium.</h1>
    <div class="centreon-body_1">
        <p>Quid philosophia Epicureum quo praesertim se se ille.</p>
    </div>
    <div class="centreon-body_2">
        <p>Efferebantur tresque saeviore urbium saeviore incohibili.</p>
    </div>
</div>
```

### Formatting

* Use 4 spaces for indentation
* Prefer dashes over camelCasing in class names.
    * class: .some-class-name
    * id: #some-id-to-an-element
* Do not use ID selectors
* When using multiple selectors in a rule declaration, give each selector its own line.
* Put a space before the opening brace { in rule declarations
* In properties, put a space after, but not before, the : character.
* Put closing braces } of rule declarations on a new line
* Put blank lines between rule declarations

```css
/* bad */
.avatar{
  border-radius:50%;
  border:2px solid white; }
.one,.selector,.per-line {
    // ...
}

/* good */
.avatar {
  border-radius: 50%;
  border: 2px solid white;
}

.one,
.selector,
.per-line {
  // ...
}
```

### Border

Use 0 instead of none to specify that a style has no border.

```css
/* bad */
.foo {
  border: none;
}

/* good */
.foo {
  border: 0;
}
```
### Module

To set a css for a module, use underscore [_] to make the separation of the module identifier and the class

```css
.module_class-name {
  color: green;
}
```

**[⬆ back to top](#coding-style-guide)**

**[← back to summary](https://github.com/centreon/centreon)**