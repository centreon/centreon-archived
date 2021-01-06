import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Grid, Typography, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';
import CopyIcon from '@material-ui/icons/FileCopy';

import {
  StatusChip,
  SeverityCode,
  IconButton,
  useSnackbar,
  Severity,
} from '@centreon/ui';
import copyToClipboard from '@centreon/ui/src/utils/copy';

import {
  labelCopyLink,
  labelLinkCopied,
  labelSomethingWentWrong,
} from '../translatedLabels';

import { DetailsSectionProps } from '.';

const useStyles = makeStyles((theme) => ({
  header: {
    height: 43,
    padding: theme.spacing(0, 1),
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: 'auto minmax(0, 1fr) auto',
    alignItems: 'center',
  },
  parent: {
    display: 'grid',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto minmax(0, 1fr)',
    alignItems: 'center',
  },
  truncated: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container spacing={2} alignItems="center" item style={{ flexGrow: 1 }}>
    <Grid item>
      <Skeleton variant="circle" width={25} height={25} />
    </Grid>
    <Grid item>
      <Skeleton width={250} height={25} />
    </Grid>
  </Grid>
);

const HeaderContent = ({ details }: DetailsSectionProps): JSX.Element => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();
  const classes = useStyles();

  const copyResourceLink = (): void => {
    try {
      copyToClipboard(window.location.href);
      showMessage({
        message: t(labelLinkCopied),
        severity: Severity.success,
      });
    } catch (_) {
      showMessage({
        message: t(labelSomethingWentWrong),
        severity: Severity.error,
      });
    }
  };

  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  return (
    <>
      {details.severity && (
        <StatusChip
          severityCode={SeverityCode.None}
          label={details.severity.level?.toString()}
        />
      )}
      <StatusChip
        severityCode={details.status.severity_code}
        label={t(details.status.name)}
      />
      <div>
        <Typography className={classes.truncated}>{details.name}</Typography>
        {details.parent && (
          <div className={classes.parent}>
            <StatusChip severityCode={details.parent.status?.severity_code} />
            <Typography variant="caption" className={classes.truncated}>
              {details.parent.name}
            </Typography>
          </div>
        )}
      </div>
      <IconButton
        size="small"
        title={t(labelCopyLink)}
        ariaLabel={t(labelCopyLink)}
        onClick={copyResourceLink}
      >
        <CopyIcon fontSize="small" />
      </IconButton>
    </>
  );
};

const Header = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.header}>
      <HeaderContent details={details} />
    </div>
  );
};

export default Header;
