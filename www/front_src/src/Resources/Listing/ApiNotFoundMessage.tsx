import * as React from 'react';

import { Link } from '@material-ui/core';

import {
  labelApiNotFoundNotUpToDate,
  labelApiNotFoundContactAdmin,
  labelApiNotFoundDocumentation,
} from '../translatedLabels';

const ApiNotFoundMessage = (): JSX.Element => {
  return (
    <>
      <p style={{ margin: 0 }}>{`${labelApiNotFoundNotUpToDate}.`}</p>
      <p style={{ margin: 0 }}>
        {`${labelApiNotFoundContactAdmin} : /opt/rh/httpd24/root/etc/httpd/conf.d/10-centreon.conf`}
      </p>
      <p style={{ margin: 0 }}>
        <Link
          href="https://docs.centreon.com/20.04/en/upgrade/upgrade-from-19-10.html#configure-apache-api-access"
          target="_blank"
          color="inherit"
        >
          {`( ${labelApiNotFoundDocumentation} )`}
        </Link>
      </p>
    </>
  );
};

export default ApiNotFoundMessage;
