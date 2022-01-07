import * as React from 'react';

import { useTranslation } from 'react-i18next';

import makeStyles from '@mui/styles/makeStyles';

import { IconButton } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  list: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
}));
interface Props {
  list: JSX.Element;
  onSwitchButtonClick: () => void;
  switchButtonIcon: JSX.Element;
  switchButtonLabel: string;
}

const Listing = ({
  list,
  switchButtonLabel,
  switchButtonIcon,
  onSwitchButtonClick,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <>
      <IconButton
        ariaLabel={t(switchButtonLabel)}
        size="large"
        title={t(switchButtonLabel)}
        onClick={onSwitchButtonClick}
      >
        {switchButtonIcon}
      </IconButton>
      <div className={classes.list}>{list}</div>
    </>
  );
};

export default Listing;
