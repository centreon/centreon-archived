import * as React from 'react';

import { useTranslation } from 'react-i18next';

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
  const { t } = useTranslation();

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
        <p>{`${t(labelSearchOnFields)}. ${t(labelSearchByRegexp)}.`}</p>
        <p>{`${t(labelSearchSyntax)} ${searchFields.join(':, ')}:.`}</p>
        <p>{t(labelSearchSomeExamples)}</p>
        <ul>
          <li>
            <b>h.name:^FR20</b>
            {` ${t(labelSearchByHostNameStartingWith)} "FR20"`}
          </li>
          <li>
            <b>s.description:ens192$</b>
            {` ${t(labelSearchByServiceDescEndingWith)} "ens192"`}
          </li>
          <li>
            <b>h.alias:prod</b>
            {` ${t(labelSearchByHostAliasContaining)} "prod"`}
          </li>
          <li>
            <b>h.address:^((?!production).)*$</b>
            {` ${t(labelSearchByHostAddressNotContaining)} "production"`}
          </li>
          <li>
            <b>h.name:^FR20 h.alias:prod</b>
            {` ${t(labelSearchByHostNameStartingWith)} "FR20" ${t(
              labelSearchAndHostAliasContaining,
            )} "prod"`}
          </li>
        </ul>
        <i>
          <b>{`${t(labelTips)}: `}</b>
          {`${t(labelGetRegexHelp)} `}
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

  const { t } = useTranslation();

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
        aria-label={t(labelSearchHelp)}
        size="small"
        onClick={toggleTooltip}
      >
        <IconHelp />
      </IconButton>
    </Tooltip>
  );
};

export default SearchHelpTooltip;
