import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { IconButton } from '@centreon/ui';

import { useStyles } from '.';

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
      <div className={classes.services}>{list}</div>
    </>
  );
};

export default Listing;
