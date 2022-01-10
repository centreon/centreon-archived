/* eslint-disable react/no-unused-class-component-methods */
/* eslint-disable react/static-property-placement */
/* eslint-disable class-methods-use-this */
/* eslint-disable react/prop-types */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/sort-comp */
/* eslint-disable react/require-default-props */

import * as React from 'react';

import PropTypes from 'prop-types';

import { normalize } from './helpers';

export default class BoundingBox extends React.Component {
  state = {
    isInViewport: null,
  };

  static propTypes = {
    children: PropTypes.oneOfType([PropTypes.element, PropTypes.func]),
    onChange: PropTypes.func,
  };

  getContainer = () => {
    return window;
  };

  roundRectDown(rect) {
    return {
      bottom: Math.floor(rect.bottom),
      left: Math.floor(rect.left),
      right: Math.floor(rect.right),
      top: Math.floor(rect.top),
    };
  }

  isIn = () => {
    const element = this.node;

    if (!element) {
      return this.state;
    }

    const rect = normalize(this.roundRectDown(element.getBoundingClientRect()));

    const windowRect = {
      bottom: window.innerHeight || document.documentElement.clientHeight,
      left: 0,
      right: window.innerWidth || document.documentElement.clientWidth,
      top: 0,
    };

    const rectBox = {
      bottom: windowRect.bottom - rect.bottom,
      left: windowRect.left - rect.left,
      offsetHeight: element.offsetHeight,
      right: windowRect.right - rect.right,
      top: windowRect.top - rect.top,
    };

    const isNotHidden = rect.height > 0 && rect.width > 0;

    const isInViewport =
      isNotHidden &&
      rect.top >= windowRect.top &&
      rect.left >= windowRect.left &&
      rect.bottom <= windowRect.bottom &&
      rect.right <= windowRect.right;

    let { state } = this;
    if (
      this.state.isInViewport !== isInViewport ||
      this.state.rectBox.top !== rectBox.top ||
      rectBox.bottom !== this.state.rectBox.bottom
    ) {
      state = {
        rectBox,
      };
      this.setState(state);
      if (this.props.onChange) this.props.onChange(isInViewport);
    }

    return state;
  };

  render() {
    if (this.props.children instanceof Function) {
      return this.props.children({
        rectBox: this.state.rectBox,
      });
    }

    return React.Children.only(this.props.children);
  }
}
