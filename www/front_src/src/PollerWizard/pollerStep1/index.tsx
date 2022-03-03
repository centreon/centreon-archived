import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Typography, Button } from '@mui/material';
import FormControlLabel from '@mui/material/FormControlLabel';
import Radio from '@mui/material/Radio';

import { postData, useRequest, TextField, SelectField } from '@centreon/ui';

import { setWizardDerivedAtom } from '../PollerAtoms';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';

const pollerWaitListEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getPollerWaitList';

interface Props {
  goToNextStep: () => void;
  goToPreviousStep: () => void;
}

interface StepOneFormData {
  centreon_central_ip: string;
  server_ip: string;
  server_name: string;
}

interface WaitList {
  ip: string;
  server_name: string;
}

const FormPollerStepOne = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [waitList, setWaitList] = React.useState<Array<WaitList> | null>(null);
  const [initialized, setInitialized] = React.useState(false);
  const [inputTypeManual, setInputTypeManual] = React.useState(true);

  const [stepOneFormData, setStepOneFormData] = React.useState<StepOneFormData>(
    {
      centreon_central_ip: '',
      server_ip: '',
      server_name: '',
    },
  );

  const { sendRequest } = useRequest<Array<unknown>>({
    request: postData,
  });

  const setWizard = useUpdateAtom(setWizardDerivedAtom);

  React.useEffect(() => {
    getWaitList();
  }, []);

  React.useEffect(() => {
    if (waitList) {
      const platform = waitList.find(
        (server) => server.ip === stepOneFormData.server_ip,
      );
      if (platform) {
        setStepOneFormData({
          ...stepOneFormData,
          server_name: platform.server_name,
        });
      }
    }
  }, [stepOneFormData.server_ip]);

  React.useEffect(() => {
    if (waitList && !initialized) {
      initializeFromRest(waitList.length > 0);
    }
    setInitialized(true);
  }, [waitList]);

  const getWaitList = (): void => {
    sendRequest({
      data: null,
      endpoint: pollerWaitListEndpoint,
    })
      .then((data): void => {
        setWaitList([
          {
            ip: '172.0.0.1',
            server_name: 'localhost',
          },
          {
            ip: '198.0.0.1',
            server_name: 'poller1',
          },
          {
            ip: '172.10.10.1',
            server_name: 'poller2',
          },
        ]);
      })
      .catch(() => {
        setWaitList([]);
      });
  };

  const initializeFromRest = (value): void => {
    setInitialized(true);
    setInputTypeManual(!value);
  };

  const handleChange = (evt): void => {
    const { value, name } = evt.target;

    setStepOneFormData({
      ...stepOneFormData,
      [name]: value,
    });
  };

  const handleSubmit = (event): void => {
    event.preventDefault();

    setWizard(stepOneFormData);
    goToNextStep();
  };

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">{t('Server Configuration')}</Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit}>
        <div className={classes.wizardRadio}>
          <FormControlLabel
            checked={inputTypeManual}
            control={<Radio color="primary" size="small" />}
            label={`${t('Create new Poller')}`}
            onClick={(): void => setInputTypeManual(true)}
          />
          <FormControlLabel
            checked={!inputTypeManual}
            control={<Radio color="primary" size="small" />}
            label={`${t('Select a Poller')}`}
            onClick={(): void => setInputTypeManual(false)}
          />
        </div>
        {inputTypeManual ? (
          <div className={classes.form}>
            <TextField
              fullWidth
              label={`${t('Server Name')}`}
              name="server_name"
              value={stepOneFormData.server_name}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              label={`${t('Server IP address')}`}
              name="server_ip"
              value={stepOneFormData.server_ip}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              label={`${t(
                'Centreon Central IP address, as seen by this server',
              )}`}
              name="centreon_central_ip"
              value={stepOneFormData.centreon_central_ip}
              onChange={handleChange}
            />
          </div>
        ) : (
          <div className={classes.form}>
            {waitList && waitList.length !== 0 ? (
              <SelectField
                fullWidth
                label={`${t('Select Pending Poller IP')}`}
                name="server_ip"
                options={waitList.map((c) => ({ id: c.ip, name: c.ip }))}
                selectedOptionId={stepOneFormData.server_ip}
                onChange={handleChange}
              />
            ) : null}
            <TextField
              fullWidth
              label={`${t('Server Name')}`}
              name="server_name"
              value={stepOneFormData.server_name}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              label={`${t('Server IP address')}`}
              name="server_ip"
              value={stepOneFormData.server_ip}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              label={`${t(
                'Centreon Central IP address, as seen by this server',
              )}`}
              name="centreon_central_ip"
              value={stepOneFormData.centreon_central_ip}
              onChange={handleChange}
            />
          </div>
        )}
        <div className={classes.formButton}>
          <Button size="small" onClick={goToPreviousStep}>
            {t('Previous')}
          </Button>
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

export default FormPollerStepOne;
