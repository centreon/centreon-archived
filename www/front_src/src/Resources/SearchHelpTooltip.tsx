import React from 'react';

import { Tooltip, makeStyles } from '@material-ui/core';
import IconHelp from '@material-ui/icons/HelpOutline';

import {
  labelSearchOnFields,
  labelSearchByRegexp,
  labelSearchSyntax,
  labelSearchSomeExamples,
  labelSearchByHostNameStartingWith,
  labelSearchByServiceDescEndingWith,
  labelSearchByHostAliasContaining,
  labelSearchByHostAddressNotContaining,
  labelSearchAndHostAliasContaining,
} from './translatedLabels';

const useStyles = makeStyles((theme) => ({
  tooltip: {
    fontSize: theme.typography.pxToRem(12),
    maxWidth: 500,
  },
  icon: {
    cursor: 'auto',
  },
}));

const searchFields = ['h.name', 'h.alias', 'h.address', 's.description'];

const Content = (): JSX.Element => (
  <>
    <p>{`${labelSearchOnFields}. ${labelSearchByRegexp}.`}</p>
    <p>{`${labelSearchSyntax} ${searchFields.join(':, ')}:.`}</p>
    <p>{labelSearchSomeExamples}</p>
    <ul>
      <li>
        <b>h.name:^FR20</b>
        {` ${labelSearchByHostNameStartingWith} "FR20"`}
      </li>
      <li>
        <b>s.description:ens192$</b>
        {` ${labelSearchByServiceDescEndingWith} "ens192"`}
      </li>
      <li>
        <b>h.alias:prod</b>
        {` ${labelSearchByHostAliasContaining} "prod"`}
      </li>
      <li>
        <b>h.address:^((?!production).)*$</b>
        {` ${labelSearchByHostAddressNotContaining} "production"`}
      </li>
      <li>
        <b>h.name:^FR20 h.alias:prod</b>
        {` ${labelSearchByHostNameStartingWith} "FR20" ${labelSearchAndHostAliasContaining} "prod"`}
      </li>
    </ul>
  </>
);

const SearchHelpTooltip = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      title={<Content />}
      classes={{ tooltip: classes.tooltip }}
      interactive
    >
      <IconHelp className={classes.icon} />
    </Tooltip>
  );
};

export default SearchHelpTooltip;
