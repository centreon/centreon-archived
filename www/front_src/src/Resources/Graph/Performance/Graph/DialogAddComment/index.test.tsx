import * as React from 'react';

import axios from 'axios';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import { Resource } from '../../../../models';
import { labelComment, labelAdd } from '../../../../translatedLabels';
import { commentEndpoint } from '../../../../Actions/api/endpoint';

import DialogAddComment from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

describe(DialogAddComment, () => {
  beforeEach(() => {
    mockedAxios.post.mockResolvedValue({});
  });

  it('sends a comment request with the given date and the typed comment', async () => {
    const date = new Date('2020-11-26T15:49:39.789Z');

    const resource = {
      id: 0,
      type: 'service',
      parent: {
        id: 1,
      },
    } as Resource;

    const onAddComment = jest.fn();

    render(
      <DialogAddComment
        date={date}
        resource={resource}
        onAddComment={onAddComment}
        onClose={jest.fn()}
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

      expect(onAddComment).toHaveBeenCalledWith(commentParameters);
    });
  });
});
