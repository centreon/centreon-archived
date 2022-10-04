import { useTranslation } from 'react-i18next';

import { Card, Grid, IconButton, Tooltip, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import IconCopyFile from '@mui/icons-material/FileCopy';

import { useCopyToClipboard } from '@centreon/ui';

import { ResourceDetails } from '../../../models';
import CommandWithArguments from '../CommandLine';
import {
  labelCommand,
  labelCommandCopied,
  labelCopy,
  labelSomethingWentWrong,
} from '../../../../translatedLabels';

interface Props {
  details: ResourceDetails;
}

const useStyles = makeStyles((theme) => ({
  commandLineCard: {
    padding: theme.spacing(1, 2, 2, 2),
  },
}));

const CommandLineCard = ({ details }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { copy } = useCopyToClipboard({
    errorMessage: t(labelSomethingWentWrong),
    successMessage: t(labelCommandCopied),
  });

  const copyCommandLine = (): Promise<void> =>
    copy(details.command_line as string);

  return (
    <Card className={classes.commandLineCard} elevation={0} sx={{ border: 0 }}>
      <Typography
        gutterBottom
        color="textSecondary"
        component="div"
        variant="body1"
      >
        <Grid container alignItems="center" spacing={1}>
          <Grid item>{t(labelCommand)}</Grid>
          <Grid item>
            <Tooltip title={labelCopy} onClick={copyCommandLine}>
              <IconButton data-testid={labelCopy} size="small">
                <IconCopyFile color="primary" fontSize="small" />
              </IconButton>
            </Tooltip>
          </Grid>
        </Grid>
      </Typography>
      <CommandWithArguments commandLine={details.command_line || ''} />
    </Card>
  );
};

export default CommandLineCard;
