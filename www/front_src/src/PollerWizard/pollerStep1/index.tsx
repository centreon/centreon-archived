import { useState, useEffect } from 'react';

import { isNil, isEmpty, values } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useUpdateAtom } from 'jotai/utils';

import { Typography } from '@mui/material';
import FormControlLabel from '@mui/material/FormControlLabel';
import Radio from '@mui/material/Radio';

import { postData, useRequest, TextField, SelectField } from '@centreon/ui';

import { setWizardDerivedAtom } from '../pollerAtoms';
import { useStyles } from '../../styles/partials/form/PollerWizardStyle';
import {
  labelServerConfiguration,
  labelCreatePoller,
  labelSelectPoller,
  labelServerName,
  labelServerIp,
  labelCentreonCentralIp,
  labelSelectPollerIp,
  labelRequired,
} from '../translatedLabels';
import { Props, WaitList, WizardButtonsTypes } from '../models';
import WizardButtons from '../forms/wizardButtons';
import { pollerWaitListEndpoint } from '../api/endpoints';

interface StepOneFormData {
  centreon_central_ip: string;
  server_ip: string;
  server_name: string;
}

const PollerWizardStepOne = ({
  goToNextStep,
  goToPreviousStep,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [waitList, setWaitList] = useState<Array<WaitList> | null>(null);
  const [initialized, setInitialized] = useState(false);
  const [inputTypeManual, setInputTypeManual] = useState(true);

  const [stepOneFormData, setStepOneFormData] = useState<StepOneFormData>({
    centreon_central_ip: '',
    server_ip: '',
    server_name: '',
  });

  const [error, setError] = useState<StepOneFormData>({
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
      [name]: isEmpty(value.trim()) ? t(labelRequired) : '',
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
      [name]: isEmpty(value.trim()) ? t(labelRequired) : '',
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

  const isFormNotCompleted = values(stepOneFormData).some((x) => x === '');
  const hasError = values(error).some((x) => x !== '');

  useEffect(() => {
    getWaitList();
  }, []);

  useEffect(() => {
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

  useEffect(() => {
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
            <SelectField
              fullWidth
              label={t(labelSelectPollerIp)}
              name="server_ip"
              options={waitListOption || []}
              selectedOptionId={stepOneFormData.server_ip}
              onChange={handleChange}
            />
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
        <WizardButtons
          disabled={isFormNotCompleted || hasError}
          goToPreviousStep={goToPreviousStep}
          type={WizardButtonsTypes.Next}
        />
      </form>
    </div>
  );
};

export default PollerWizardStepOne;
