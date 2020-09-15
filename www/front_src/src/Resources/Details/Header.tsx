import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Grid, Typography, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';
import LinkIcon from '@material-ui/icons/Link';

import {
  StatusChip,
  SeverityCode,
  IconButton,
  useSnackbar,
  Severity,
} from '@centreon/ui';
import copyToClipboard from '@centreon/ui/src/utils/copy';

import { DetailsSectionProps } from '.';
import {
  labelCopyLink,
  labelLinkCopied,
  labelSomethingWentWrong,
} from '../translatedLabels';

const useStyles = makeStyles((theme) => ({
  header: {
    height: 60,
    paddingRight: theme.spacing(1),
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
        {details.severity && (
          <StatusChip
            severityCode={SeverityCode.None}
            label={details.severity.level?.toString()}
          />
        )}
      </Grid>
      <Grid item>
        <StatusChip
          severityCode={details.status.severity_code}
          label={details.status.name}
        />
      </Grid>
      <Grid item style={{ flexGrow: 1 }}>
        <Grid container direction="column">
          <Grid item>
            <Typography>{details.name}</Typography>
          </Grid>
          {details.parent && (
            <Grid item container spacing={1}>
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
          size="small"
          title={t(labelCopyLink)}
          ariaLabel={t(labelCopyLink)}
          onClick={copyResourceLink}
        >
          <LinkIcon />
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
      spacing={2}
      alignItems="center"
      className={classes.header}
    >
      <HeaderContent details={details} />
    </Grid>
  );
};

export default Header;
