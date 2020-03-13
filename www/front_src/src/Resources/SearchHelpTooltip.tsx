import React from 'react';

import { Tooltip, makeStyles } from '@material-ui/core';
import IconHelp from '@material-ui/icons/HelpOutline';

import {
  labelSearchOnFields,
  labelSearchByHostName,
  labelUsePartialQuery,
  labelSearchByServiceStartingWith,
  labelSearchByHostAliasEndingWith,
} from './translatedLabels';

const useStyles = makeStyles((theme) => ({
  tooltip: {
    fontSize: theme.typography.pxToRem(12),
  },
}));

const Content = (): JSX.Element => (
  <>
    <p>{labelSearchOnFields}</p>
    <ul>
      <li>host.name</li>
      <li>host.alias</li>
      <li>host.address</li>
      <li>service.description</li>
    </ul>
    <p>{`${labelSearchByHostName} host.name:hostname`}</p>
    <p>{labelUsePartialQuery}</p>
    <ul>
      <li>{`${labelSearchByServiceStartingWith} service.description:^centreon`}</li>
      <li>{`${labelSearchByHostAliasEndingWith} host.alias:(fr|us)-server`}</li>
    </ul>
  </>
);

const SearchHelpTooltip = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip title={<Content />} classes={{ tooltip: classes.tooltip }}>
      <IconHelp />
    </Tooltip>
  );
};

export default SearchHelpTooltip;
