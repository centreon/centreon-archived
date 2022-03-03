import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { equals } from 'ramda';

import { Button, Typography } from '@mui/material';
import RadioGroup from '@mui/material/RadioGroup';
import FormControlLabel from '@mui/material/FormControlLabel';
import Radio from '@mui/material/Radio';

import {
  labelAddACentreonPoller,
  labelAddACentreonRemoteServer,
} from '../translatedLabels';
import { ServerType } from '../models';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';

interface Props {
  changeServerType: (type: ServerType) => void;
  goToNextStep: () => void;
}

const ServerConfigurationWizard = ({
  changeServerType,
  goToNextStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [serverType, setServerType] = React.useState<number>(1);

  const handleSubmit = (event): void => {
    event.preventDefault();

    if (equals(serverType, 1)) {
      changeServerType(ServerType.Remote);
    }
    if (equals(serverType, 2)) {
      changeServerType(ServerType.Poller);
    }

    goToNextStep();
  };

  const configurationTypes = [
    {
      label: labelAddACentreonRemoteServer,
      value: 1,
    },
    {
      label: labelAddACentreonPoller,
      value: 2,
    },
  ];

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">{t('Choose a server type')}</Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit}>
        <RadioGroup defaultValue="1" name="server_type">
          {configurationTypes.map((type) => (
            <FormControlLabel
              checked={serverType === type.value}
              control={<Radio color="primary" size="small" />}
              key={type.value}
              label={type.label}
              value={type.value}
              onClick={(): void => setServerType(type.value)}
            />
          ))}
        </RadioGroup>
        <div className={classes.formButton}>
          <Button
            color="primary"
            size="small"
            type="submit"
            variant="contained"
          >
            {t('Next')}
          </Button>
        </div>
      </form>
    </div>
  );
};

export default ServerConfigurationWizard;
