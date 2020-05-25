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
  tooltip: {
    backgroundColor: theme.palette.common.white,
    color: theme.palette.text.primary,
    boxShadow: theme.shadows[3],
    fontSize: theme.typography.pxToRem(12),
    maxWidth: 500,
    padding: theme.spacing(1, 2, 1, 1),
  },
  buttonClose: {
    position: 'absolute',
    right: theme.spacing(0.5),
  },
}));

const searchFields = ['h.name', 'h.alias', 'h.address', 's.description'];

interface ContentProps {
  onClose: (event) => void;
}

const Content = ({ onClose }: ContentProps): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <IconButton
        size="small"
        onClick={onClose}
        className={classes.buttonClose}
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
            target="_blank"
            rel="noopener noreferrer"
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
      open={open}
      title={<Content onClose={closeTooltip} />}
      classes={{ tooltip: classes.tooltip }}
      interactive
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
