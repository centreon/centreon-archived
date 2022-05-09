import { equals, isNil, not } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Typography, Paper, Grid, Alert } from '@mui/material';
import Chip from '@mui/material/Chip';
import CheckIcon from '@mui/icons-material/Check';
import SmsFailedIcon from '@mui/icons-material/SmsFailed';

import { ContentWithCircularLoading } from '@centreon/ui';

import { useStylesWithProps } from '../../styles/partials/form/PollerWizardStyle';
import {
  labelCreatingExportTask,
  labelGeneratingExportFiles,
} from '../../PollerWizard/translatedLabels';

interface Props {
  error: string | null;
  formTitle: string;
  statusCreating: boolean | null;
  statusGenerating: boolean | null;
}

export default ({
  formTitle,
  statusCreating,
  statusGenerating,
  error,
}: Props): JSX.Element => {
  const classes = useStylesWithProps({ statusCreating, statusGenerating });

  const { t } = useTranslation();

  const loading = isNil(statusCreating) || isNil(statusGenerating);
  const hasError =
    (equals(statusCreating, false) || equals(statusGenerating, false)) && error;

  return (
    <Paper>
      <div>
        <div className={classes.formHeading}>
          <Typography variant="h6">{formTitle}</Typography>
        </div>
        <p className={classes.formText}>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span className={classes.statusCreating}>
              {not(isNil(statusCreating)) ? (
                <Typography variant="body2">
                  <Chip
                    color={statusCreating ? 'success' : 'error'}
                    icon={statusCreating ? <CheckIcon /> : <SmsFailedIcon />}
                    label={t(labelCreatingExportTask)}
                    style={{ width: '100%' }}
                  />
                </Typography>
              ) : (
                '...'
              )}
            </span>
          </ContentWithCircularLoading>
        </p>
        <p className={classes.formText}>
          <ContentWithCircularLoading alignCenter loading={loading}>
            <span className={classes.statusGenerating}>
              {not(isNil(statusGenerating)) ? (
                <Typography variant="body2">
                  <Chip
                    color={statusGenerating ? 'success' : 'error'}
                    icon={statusGenerating ? <CheckIcon /> : <SmsFailedIcon />}
                    label={t(labelGeneratingExportFiles)}
                    style={{ width: '100%' }}
                  />
                </Typography>
              ) : (
                '...'
              )}
            </span>
          </ContentWithCircularLoading>
        </p>
        {hasError && (
          <Grid item>
            <Alert severity="error">
              <Typography>{error}</Typography>
            </Alert>
          </Grid>
        )}
      </div>
    </Paper>
  );
};
