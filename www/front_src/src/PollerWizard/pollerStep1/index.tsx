import * as React from 'react';

import { isNil } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Typography, Button } from '@mui/material';
import FormControlLabel from '@mui/material/FormControlLabel';
import Radio from '@mui/material/Radio';

import { postData, useRequest, TextField, SelectField } from '@centreon/ui';

import { setWizardDerivedAtom } from '../PollerAtoms';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';
import {
  labelServerConfiguration,
  labelCreatePoller,
  labelSelectPoller,
  labelServerName,
  labelServerIp,
  labelCentreonCentralIp,
  labelNext,
  labelPrevious,
  labelSelectPollerIp,
  labelRequired,
} from '../translatedLabels';
import { Props, WaitList } from '../models';

const pollerWaitListEndpoint =
  './api/internal.php?object=centreon_configuration_remote&action=getPollerWaitList';

interface StepOneFormData {
  centreon_central_ip: string;
  server_ip: string;
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

  const [error, setError] = React.useState<StepOneFormData>({
    centreon_central_ip: '',
    server_ip: '',
    server_name: '',
  });

  const { sendRequest } = useRequest<Array<WaitList>>({
    request: postData,
  });

  const setWizard = useUpdateAtom(setWizardDerivedAtom);

  const getWaitList = (): void => {
    sendRequest({
      data: null,
      endpoint: pollerWaitListEndpoint,
    })
      .then((data): void => {
        setWaitList(data);
      })
      .catch(() => {
        setWaitList([]);
      });
  };

  const initializeFromRest = (value): void => {
    setInitialized(true);
    setInputTypeManual(!value);
  };

  const handleChange = (event): void => {
    const { value, name } = event.target;

    setError({
      ...error,
      [name]: value.trim() === '' ? t(labelRequired) : '',
    });

    setStepOneFormData({
      ...stepOneFormData,
      [name]: value,
    });
  };

  const handleBlur = (event): void => {
    const { value, name } = event.target;

    setError({
      ...error,
      [name]: value.trim() === '' ? t(labelRequired) : '',
    });
  };

  const handleSubmit = (event): void => {
    event.preventDefault();

    setWizard(stepOneFormData);
    goToNextStep();
  };

  const waitListOption = waitList?.map((c) => ({ id: c.ip, name: c.ip }));

  const getError = (stateName): string | undefined =>
    error[stateName].length > 0 ? error[stateName] : undefined;

  const nextDisabled =
    Object.values(stepOneFormData).some((x) => x === '') ||
    Object.values(error).some((x) => x !== '');

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
    if (isNil(waitList) || initialized) {
      return;
    }
    initializeFromRest(waitList.length > 0);
  }, [waitList]);

  return (
    <div>
      <div className={classes.formHeading}>
        <Typography variant="h6">{t(labelServerConfiguration)}</Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit}>
        <div className={classes.wizardRadio}>
          <FormControlLabel
            checked={inputTypeManual}
            control={<Radio color="primary" size="small" />}
            label={`${t(labelCreatePoller)}`}
            onClick={(): void => setInputTypeManual(true)}
          />
          <FormControlLabel
            checked={!inputTypeManual}
            control={<Radio color="primary" size="small" />}
            label={`${t(labelSelectPoller)}`}
            onClick={(): void => setInputTypeManual(false)}
          />
        </div>
        {inputTypeManual ? (
          <div className={classes.form}>
            <TextField
              fullWidth
              required
              error={getError('server_name')}
              label={t(labelServerName)}
              name="server_name"
              value={stepOneFormData.server_name}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('server_ip')}
              label={t(labelServerIp)}
              name="server_ip"
              value={stepOneFormData.server_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('centreon_central_ip')}
              label={t(labelCentreonCentralIp)}
              name="centreon_central_ip"
              value={stepOneFormData.centreon_central_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
          </div>
        ) : (
          <div className={classes.form}>
            {waitListOption && waitListOption.length !== 0 && (
              <SelectField
                fullWidth
                label={t(labelSelectPollerIp)}
                name="server_ip"
                options={waitListOption}
                selectedOptionId={stepOneFormData.server_ip}
                onChange={handleChange}
              />
            )}
            <TextField
              fullWidth
              required
              error={getError('server_name')}
              label={t(labelServerName)}
              name="server_name"
              value={stepOneFormData.server_name}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('server_ip')}
              label={t(labelServerIp)}
              name="server_ip"
              value={stepOneFormData.server_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
            <TextField
              fullWidth
              required
              error={getError('centreon_central_ip')}
              label={t(labelCentreonCentralIp)}
              name="centreon_central_ip"
              value={stepOneFormData.centreon_central_ip}
              onBlur={handleBlur}
              onChange={handleChange}
            />
          </div>
        )}
        <div className={classes.formButton}>
          <Button size="small" onClick={goToPreviousStep}>
            {t(labelPrevious)}
          </Button>
          <Button
            color="primary"
            disabled={nextDisabled}
            size="small"
            type="submit"
            variant="contained"
          >
            {t(labelNext)}
          </Button>
        </div>
      </form>
    </div>
  );
};

export default FormPollerStepOne;
