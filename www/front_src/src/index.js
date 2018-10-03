import React from "react";
import ReactDOM from "react-dom";
import "./App.css";
import "loaders.css/loaders.min.css";
import AppProvider from "./Provider";
import Favicon from 'react-favicon'; 

ReactDOM.render(<React.Fragment><Favicon url='./img/favicon.ico' /><AppProvider /></React.Fragment>, document.getElementById("root"));
