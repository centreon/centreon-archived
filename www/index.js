import React from 'react'
import ReactDOM from 'react-dom'
import TopHeader from './frontSrc/Header/TopHeaderContainer'

ReactDOM.hydrate(
  <TopHeader />,
  document.getElementById('header-react')
);
