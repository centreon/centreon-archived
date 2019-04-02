import React from "react";
import Helmet from "react-helmet";

const DocHead = () => (
  <Helmet titleTemplate="%s | HopKidz" defaultTitle="HopKidz">
    <title>Centreon - IT &amp; Network Monitoring</title>
    <link rel="shortcut icon" href="./img/favicon.ico" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta
      name="Generator"
      content="Centreon - Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved."
    />
    <meta name="robots" content="index, nofollow" />
  </Helmet>
);

export default DocHead;
