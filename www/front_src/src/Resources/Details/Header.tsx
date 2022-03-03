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
  copyToClipboard,
} from '@centreon/ui';

import {
  labelCopyLink,
  labelLinkCopied,
  labelSomethingWentWrong,
} from '../translatedLabels';

import { DetailsSectionProps } from '.';

const useStyles = makeStyles((theme) => ({
  header: {
    height: 60,
    paddingRight: theme.spacing(1),
  },
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container item alignItems="center" spacing={2} style={{ flexGrow: 1 }}>
    <Grid item>
      <Skeleton height={25} variant="circle" width={25} />
    </Grid>
    <Grid item>
      <Skeleton height={25} width={250} />
    </Grid>
  </Grid>
);

const HeaderContent = ({ details }: DetailsSectionProps): JSX.Element => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();

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
      <Grid item>
        {details.severity_level && (
          <StatusChip
            label={details.severity_level.toString()}
            severityCode={SeverityCode.None}
          />
        )}
      </Grid>
      <Grid item>
        <StatusChip
          label={t(details.status.name)}
          severityCode={details.status.severity_code}
        />
      </Grid>
      <Grid item style={{ flexGrow: 1 }}>
        <Grid container direction="column">
          <Grid item>
            <Typography>{details.name}</Typography>
          </Grid>
          {details.parent && (
            <Grid container item spacing={1}>
              <Grid item>
                <StatusChip
                  severityCode={details.parent.status?.severity_code}
                />
              </Grid>
              <Grid item>
                <Typography variant="caption">{details.parent.name}</Typography>
              </Grid>
            </Grid>
          )}
        </Grid>
      </Grid>
      <Grid item>
        <IconButton
          ariaLabel={t(labelCopyLink)}
          data-testid={labelCopyLink}
          size="small"
          title={t(labelCopyLink)}
          onClick={copyResourceLink}
        >
          <CopyIcon fontSize="small" />
        </IconButton>
      </Grid>
    </>
  );
};

const Header = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles();

  return (
    <Grid
      container
      item
      alignItems="center"
      className={classes.header}
      spacing={2}
    >
      <HeaderContent details={details} />
    </Grid>
  );
};

export default Header;
