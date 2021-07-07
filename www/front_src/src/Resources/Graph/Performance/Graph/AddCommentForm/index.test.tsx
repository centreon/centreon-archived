import * as React from 'react';

import axios from 'axios';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import { Resource } from '../../../../models';
import { labelComment, labelAdd } from '../../../../translatedLabels';
import { commentEndpoint } from '../../../../Actions/api/endpoint';

import AddCommentForm from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

describe(AddCommentForm, () => {
  beforeEach(() => {
    mockedAxios.post.mockResolvedValue({});
  });

  it('sends a comment request with the given date and the typed comment', async () => {
    const date = new Date('2020-11-26T15:49:39.789Z');

    const resource = {
      id: 0,
      parent: {
        id: 1,
      },
      type: 'service',
    } as Resource;

    const onSuccess = jest.fn();

    render(
      <AddCommentForm
        date={date}
        resource={resource}
        onClose={jest.fn()}
        onSuccess={onSuccess}
      />,
    );

    expect(screen.getByText(labelAdd).parentElement).toBeDisabled();

    userEvent.type(screen.getByLabelText(labelComment), 'plop');

    userEvent.click(screen.getByText(labelAdd));

    const commentParameters = {
      comment: 'plop',
      date: '2020-11-26T15:49:39Z',
    };

    await waitFor(() => {
      expect(mockedAxios.post).toHaveBeenCalledWith(
        commentEndpoint,
        {
          resources: [
            {
              ...resource,
              ...commentParameters,
            },
          ],
        },
        expect.anything(),
      );

      expect(onSuccess).toHaveBeenCalledWith(commentParameters);
    });
  });
});
