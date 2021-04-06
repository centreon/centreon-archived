import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles } from '@material-ui/core';

import { IconButton } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  list: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
}));
interface Props {
  list: JSX.Element;
  switchButtonLabel: string;
  switchButtonIcon: JSX.Element;
  onSwitchButtonClick: () => void;
}

const Listing = ({
  list,
  switchButtonLabel,
  switchButtonIcon,
  onSwitchButtonClick,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <>
      <IconButton
        title={t(switchButtonLabel)}
        ariaLabel={t(switchButtonLabel)}
        onClick={onSwitchButtonClick}
      >
        {switchButtonIcon}
      </IconButton>
      <div className={classes.list}>{list}</div>
    </>
  );
};

export default Listing;
