import * as React from 'react';

import { Tooltip, IconButton, Box, Link, makeStyles } from '@material-ui/core';
import IconHelp from '@material-ui/icons/HelpOutline';
import IconClose from '@material-ui/icons/HighlightOff';

import {
  labelSearchHelp,
  labelSearchOnFields,
  labelSearchByRegexp,
  labelSearchSyntax,
  labelSearchSomeExamples,
  labelSearchByHostNameStartingWith,
  labelSearchByServiceDescEndingWith,
  labelSearchByHostAliasContaining,
  labelSearchByHostAddressNotContaining,
  labelSearchAndHostAliasContaining,
  labelTips,
  labelGetRegexHelp,
} from '../translatedLabels';

const useStyles = makeStyles((theme) => ({
  buttonClose: {
    position: 'absolute',
    right: theme.spacing(0.5),
  },
  tooltip: {
    backgroundColor: theme.palette.common.white,
    boxShadow: theme.shadows[3],
    color: theme.palette.text.primary,
    fontSize: theme.typography.pxToRem(12),
    maxWidth: 500,
    padding: theme.spacing(1, 2, 1, 1),
  },
}));

const searchFields = [
  'h.name',
  'h.alias',
  'h.address',
  's.description',
  'information',
];

interface ContentProps {
  onClose: (event) => void;
}

const Content = ({ onClose }: ContentProps): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <IconButton
        className={classes.buttonClose}
        size="small"
        onClick={onClose}
      >
        <IconClose fontSize="small" />
      </IconButton>
      <Box padding={1}>
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
        <i>
          <b>{`${labelTips}: `}</b>
          {`${labelGetRegexHelp} `}
          <Link
            href="https://regex101.com"
            rel="noopener noreferrer"
            target="_blank"
          >
            regex101.com
          </Link>
        </i>
      </Box>
    </>
  );
};

const SearchHelpTooltip = (): JSX.Element => {
  const classes = useStyles();

  const [open, setOpen] = React.useState(false);

  const toggleTooltip = (): void => {
    setOpen(!open);
  };

  const closeTooltip = (): void => {
    setOpen(false);
  };

  return (
    <Tooltip
      interactive
      classes={{ tooltip: classes.tooltip }}
      open={open}
      title={<Content onClose={closeTooltip} />}
    >
      <IconButton
        aria-label={labelSearchHelp}
        size="small"
        onClick={toggleTooltip}
      >
        <IconHelp />
      </IconButton>
    </Tooltip>
  );
};

export default SearchHelpTooltip;
